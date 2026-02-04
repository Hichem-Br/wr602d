#!/bin/bash
docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/security/login.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}Log in{% endblock %}

{% block body %}
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="post">
                    {% if error %}
                        <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
                    {% endif %}

                    {% if app.user %}
                        <div class="mb-3">
                            You are logged in as {{ app.user.userIdentifier }}, <a href="{{ path('app_logout') }}">Logout</a>
                        </div>
                    {% endif %}

                    <h1 class="h3 mb-3 fw-normal text-center">Please sign in</h1>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Email</label>
                        <input type="email" value="{{ last_username }}" name="_username" id="username" class="form-control" autocomplete="email" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="_password" id="password" class="form-control" autocomplete="current-password" required>
                    </div>

                    <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" type="submit">
                            Sign in
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="small">Don't have an account? <a href="{{ path('app_register') }}">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}
TWIG_EOF
