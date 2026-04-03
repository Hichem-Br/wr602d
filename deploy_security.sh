#!/bin/bash

# Create SecurityController
docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/SecurityController.php" <<'PHP_EOF'
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
PHP_EOF

# Create Templates Directory
docker exec -w /var/www symfony-web-v2 mkdir -p templates/security

# Create Login Template
docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/security/login.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}Log in{% endblock %}

{% block body %}
<div class="container">
    <form method="post">
        {% if error %}
            <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
        {% endif %}

        <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
        <label for="username">Email</label>
        <input type="email" value="{{ last_username }}" name="_username" id="username" class="form-control" autocomplete="email" required autofocus>
        <label for="password">Password</label>
        <input type="password" name="_password" id="password" class="form-control" autocomplete="current-password" required>

        <input type="hidden" name="_csrf_token"
               value="{{ csrf_token('authenticate') }}"
        >

        <button class="btn btn-lg btn-primary" type="submit">
            Sign in
        </button>
    </form>
</div>
{% endblock %}
TWIG_EOF
