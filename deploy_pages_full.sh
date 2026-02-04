#!/bin/bash

# Create Controllers
docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/HomeController.php" <<'PHP_EOF'
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
PHP_EOF

docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/SubscriptionController.php" <<'PHP_EOF'
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractController
{
    public function changeSubscription(): Response
    {
        return $this->render('subscription/change.html.twig');
    }
}
PHP_EOF

docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/HistoryController.php" <<'PHP_EOF'
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HistoryController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('history/index.html.twig');
    }
}
PHP_EOF

# Create Templates Directories
docker exec -w /var/www symfony-web-v2 mkdir -p templates/home templates/subscription templates/history

# Create Templates
docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/home/index.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}Home{% endblock %}

{% block body %}
<div class="container">
    <h1>Welcome to the PDF Generator</h1>
    <p>This is the home page.</p>
</div>
{% endblock %}
TWIG_EOF

docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/subscription/change.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}Change Subscription{% endblock %}

{% block body %}
<div class="container">
    <h1>Change Subscription</h1>
    <p>Subscription management page.</p>
</div>
{% endblock %}
TWIG_EOF

docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/history/index.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}History{% endblock %}

{% block body %}
<div class="container">
    <h1>Generation History</h1>
    <p>History of generated PDFs will appear here.</p>
</div>
{% endblock %}
TWIG_EOF
