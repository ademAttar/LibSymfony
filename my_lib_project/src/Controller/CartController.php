<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        $panierData = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $book = $em->getRepository(Book::class)->find($id);

            if (!$book) {
                unset($panier[$id]);
                $this->addFlash('warning', 'A book was removed from your cart as it no longer exists.');
                continue;
            }

            if ($book->getPrice() === null) {
                unset($panier[$id]);
                $title = $book->getTitle() ?? 'Untitled book';
                $this->addFlash('warning', sprintf('"%s" was removed from your cart as its price is not listed.', $title));
                continue;
            }

            $panierData[] = [
                'book' => $book,
                'quantity' => $quantity,
            ];
            $total += $book->getPrice() * $quantity;
        }

        $session->set('panier', $panier);

        return $this->render('cart/index.html.twig', [
            'items' => $panierData,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'])]
    public function add(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);

        if (!$book) {
            $this->addFlash('error', 'The book does not exist.');
            return $this->redirectToRoute('app_book_index2');
        }

        if ($book->getPrice() === null) {
            $this->addFlash('error', 'Cannot add this book to the cart as its price is not listed.');
            return $this->redirectToRoute('app_book_index2');
        }

        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if (isset($panier[$id]) && $panier[$id] >= 100) {
            $this->addFlash('error', 'You cannot add more than 100 copies of this book.');
            return $this->redirectToRoute('app_cart');
        }

        $panier[$id] = ($panier[$id] ?? 0) + 1;
        $session->set('panier', $panier);

        $title = $book->getTitle() ?? 'Untitled book';
        $this->addFlash('success', sprintf('Added "%s" to your cart.', $title));

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'])]
    public function remove(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);

        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            $title = $book ? ($book->getTitle() ?? 'Untitled book') : 'Unknown book';
            unset($panier[$id]);
            $session->set('panier', $panier);
            $this->addFlash('success', sprintf('Removed "%s" from your cart.', $title));
        } else {
            $this->addFlash('error', 'The book is not in your cart.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $book = $em->getRepository(Book::class)->find($id);
        if (!$book) {
            $this->addFlash('error', 'The book does not exist.');
            return $this->redirectToRoute('app_cart');
        }

        $quantity = (int) $request->request->get('quantity', 1);
        if ($quantity < 1) {
            return $this->redirectToRoute('app_cart_remove', ['id' => $id]);
        }

        if ($quantity > 100) {
            $this->addFlash('error', 'You cannot add more than 100 copies of this book.');
            return $this->redirectToRoute('app_cart');
        }

        $session = $request->getSession();
        $panier = $session->get('panier', []);
        $panier[$id] = $quantity;
        $session->set('panier', $panier);

        $title = $book->getTitle() ?? 'Untitled book';
        $this->addFlash('success', sprintf('Updated quantity for "%s".', $title));
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout')]
    public function checkout(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        // Get user from session instead of security context
        $userSession = $session->get('user');
        if (!$userSession) {
            $this->addFlash('error', 'You need to be logged in to checkout.');
            return $this->redirectToRoute('app_login');
        }

        if (empty($panier)) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        // Fetch the full user entity from database
        $user = $em->getRepository(User::class)->find($userSession['id']);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_login');
        }

        $order = new Order();
        $order->setUser($user);
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $book = $em->getRepository(Book::class)->find($id);
            if (!$book || $book->getPrice() === null) {
                unset($panier[$id]);
                continue;
            }

            $orderItem = new OrderItem();
            $orderItem->setBook($book);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($book->getPrice());
            $orderItem->setOrder($order);

            $order->addOrderItem($orderItem);
            $em->persist($orderItem);

            $total += $book->getPrice() * $quantity;
        }

        $order->setTotal($total);
        $em->persist($order);
        $em->flush();

        $session->remove('panier');
        $this->addFlash('success', 'Your order has been placed successfully!');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/my-orders', name: 'app_user_orders')]
    public function userOrders(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userSession = $session->get('user');

        if (!$userSession) {
            $this->addFlash('error', 'Please login to view your orders.');
            return $this->redirectToRoute('app_login');
        }

        $orders = $em->getRepository(Order::class)->findByUser($userSession['id']);

        return $this->render('cart/user_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

}