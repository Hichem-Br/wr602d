#!/bin/bash
docker exec -i symfony-web-v2 bash -c "cat > /var/www/config/packages/security.yaml" <<'YAML_EOF'
security:
    # https://symfony.com/doc/current/security.html#registering-the-user-hashing-passwords
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    # https://symfony.com/doc/current/security.html#loading-the-user-the-user-provider
    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_profiler|_wdt|assets|build)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                enable_csrf: true
                default_target_path: homepage
            logout:
                path: /logout
                target: homepage

            # activate different ways to authenticate
            # https://symfony.com/doc/current/security.html#the-firewall

            # https://symfony.com/doc/current/security/impersonating_user.html
            # switch_user: true

    # Easy way to control access for large sections of your site
    # Note: Only the *first* matching rule is applied
    access_control:
        - { path: ^/pdf/generate, roles: ROLE_USER }
        - { path: ^/history, roles: ROLE_USER }
        - { path: ^/subscription/change, roles: ROLE_USER }
        # - { path: ^/admin, roles: ROLE_ADMIN }
        # - { path: ^/profile, roles: ROLE_USER }

when@test:
    security:
        password_hashers:
            # Password hashers are resource-intensive by design to ensure security.
            # In tests, it's safe to reduce their cost to improve performance.
            Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
                algorithm: auto
                cost: 4 # Lowest possible value for bcrypt
                time_cost: 3 # Lowest possible value for argon
                memory_cost: 10 # Lowest possible value for argon
YAML_EOF
