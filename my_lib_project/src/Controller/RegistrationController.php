<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the role to "user" by default
            $user->setRole('user');

            // Save the user to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Add a success message
            $this->addFlash('success', 'Registration successful!');

            // Redirect to the home page
            return $this->redirectToRoute('home');
        }

        return $this->render('user/signUp.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
