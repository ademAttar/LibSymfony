<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];
            $password = $data['password'];

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user && $user->getPassword() === $password) {
                $session = $request->getSession();
                $session->set('user', [
                    'id' => $user->getId(),
                    'role' => $user->getRole(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail(),
                ]);

                $this->addFlash('success', 'Login successful!');

                if ($user->getRole() === 'admin') {
                    return $this->redirectToRoute('app_book_index');
                } else {
                    return $this->redirectToRoute('app_book_index2');
                }
            } else {
                $this->addFlash('error', 'Invalid email or password.');
            }
        }

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('user');

        $this->addFlash('success', 'Logged out successfully!');

        return $this->redirectToRoute('home');
    }
}
