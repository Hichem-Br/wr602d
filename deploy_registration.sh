#!/bin/bash

# Create RegistrationController
docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/RegistrationController.php" <<'PHP_EOF'
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
PHP_EOF

# Create Registration Template
docker exec -w /var/www symfony-web-v2 mkdir -p templates/registration
docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/registration/register.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}Register{% endblock %}

{% block body %}
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3 fw-normal text-center">Register</h1>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" required autofocus>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" name="firstname" id="firstname" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" name="lastname" id="lastname" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" type="submit">Create Account</button>
                    </div>

                    <div class="text-center mt-3">
                        <p class="small">Already have an account? <a href="{{ path('app_login') }}">Log in</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG_EOF

# Update Routes
# Appending to routes.yaml via tee -a (inside container)
docker exec -i symfony-web-v2 bash -c "cat >> /var/www/config/routes.yaml" <<'YAML_EOF'

app_register:
    path: /register
    controller: App\Controller\RegistrationController::register
YAML_EOF
