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

            // Find user by email
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user && $user->getPassword() === $password) {
                // Save user data in session
                $session = $request->getSession();
                $session->set('user', [
                    'id' => $user->getId(),
                    'role' => $user->getRole(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail(),
                ]);

                // Successful login
                $this->addFlash('success', 'Login successful!');

                // Redirect based on role
                if ($user->getRole() === 'admin') {
                    return $this->redirectToRoute('app_book_index');
                } else {
                    return $this->redirectToRoute('app_book_index2');
                }
            } else {
                // Invalid credentials
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

//    #[Route('/admin/dashboard/{id}', name: 'app_admin_dashboard')]
//    public function adminDashboard(int $id, EntityManagerInterface $entityManager): Response
//    {
//        $user = $entityManager->getRepository(User::class)->find($id);
//
//        if (!$user || $user->getRole() !== 'admin') {
//            throw $this->createNotFoundException('Admin user not found');
//        }
//
//        return $this->render('admin/dashboard.html.twig', [
//            'user' => $user,
//        ]);
//    }
//
//    #[Route('/user/dashboard/{id}', name: 'app_user_dashboard')]
//    public function userDashboard(int $id, EntityManagerInterface $entityManager): Response
//    {
//        $user = $entityManager->getRepository(User::class)->find($id);
//
//        if (!$user || $user->getRole() !== 'user') {
//            throw $this->createNotFoundException('User not found');
//        }
//
//        return $this->render('user/dashboard.html.twig', [
//            'user' => $user,
//        ]);
//    }
}
