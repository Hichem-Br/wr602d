<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Plan; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');

            if ($email && $plainPassword) {
                $user = new User();
                $user->setEmail($email);
                $user->setFirstname($firstname ?? '');
                $user->setLastname($lastname ?? '');
                $user->setRoles(['ROLE_USER']);

                // Hash the password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );

                // Assign default plan (Plan ID 1 = Free, usually)
                // Ideally fetching by name 'Free', but for simplicity assuming ID 1 exists from fixtures
                $defaultPlan = $entityManager->getRepository(Plan::class)->find(1);
                if ($defaultPlan) {
                   // $user->setPlan($defaultPlan); // Assuming User has setPlan, logic TBD based on User entity review
                }

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Account created successfully! Please login.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Please verify your information.');
            }
        }

        return $this->render('registration/register.html.twig');
    }
}
