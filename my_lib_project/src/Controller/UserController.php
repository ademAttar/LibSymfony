<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(Request $request): Response
    {
        $session = $request->getSession();
        $user = $session->get('user');

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view your profile.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('book/profile.html.twig');
    }
}
