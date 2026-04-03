#!/bin/bash
docker exec -i symfony-web-v2 bash -c "cat > /var/www/config/routes.yaml" <<'YAML_EOF'
# config/routes.yaml

app_login:
    path: /login
    controller: App\Controller\SecurityController::login

homepage:
    path: /
    controller: App\Controller\HomeController::index

subscription_change:
    path: /subscription/change
    controller: App\Controller\SubscriptionController::changeSubscription

pdf_generation:
    path: /pdf/generate
    controller: App\Controller\PdfController::generate

history:
    path: /history
    controller: App\Controller\HistoryController::index
YAML_EOF
