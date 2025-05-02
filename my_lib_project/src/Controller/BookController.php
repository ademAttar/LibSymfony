<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book')]
class BookController extends AbstractController
{

    #[Route('/', name: 'app_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

//    #[Route('/{id}', name: 'app_book_show', methods: ['GET'])]
//    #[ParamConverter('book', class: Book::class)]
//    public function show(Book $book): Response
//    {
//        return $this->render('book/show.html.twig', [
//            'book' => $book,
//        ]);
//    }

    #[Route('/book/{id}', name: 'app_book_show')]
    #[ParamConverter('book', class: Book::class)]
    public function show(Book $book): Response
    {
        // Vérifie si l'objet book existe
        if ($book === null) {
            throw $this->createNotFoundException('Le livre demandé n\'existe pas.');
        }

        // Affiche le détail du livre
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/client/{id}', name: 'app_book_show2', methods: ['GET'])]
    public function showClient(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé.');
        }

        return $this->render('book/show2.html.twig', [
            'book' => $book,
        ]);
    }


//    #[Route('/bookliste', name: 'app_book_index2')]
//    public function indexClient(BookRepository $bookRepository): Response
//    {
//        $books = $bookRepository->findAll();
//        return $this->render('book/index2.html.twig', [
//            'books' => $books,
//        ]);
//    }
    #[Route('/bookliste', name: 'app_book_index2')]
    public function indexClient(BookRepository $bookRepository): Response
    {
        // Récupère tous les livres depuis la base de données
        $books = $bookRepository->findAll();

        // Retourne la vue avec les livres
        return $this->render('book/index2.html.twig', [
            'books' => $books,
        ]);
    }

}
