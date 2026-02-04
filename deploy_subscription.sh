#!/bin/bash

# Subscription Controller
docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/SubscriptionController.php" <<'PHP_EOF'
<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SubscriptionController extends AbstractController
{
    #[Route('/subscription/change', name: 'app_subscription_change')]
    public function changeSubscription(PlanRepository $planRepository): Response
    {
        $plans = $planRepository->findAll();

        return $this->render('subscription/change.html.twig', [
            'plans' => $plans,
        ]);
    }
}
PHP_EOF

# Subscription Template
docker exec -w /var/www symfony-web-v2 mkdir -p templates/subscription
docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/subscription/change.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}Subscription Plans{% endblock %}

{% block body %}
<div class="pricing-header p-3 pb-md-4 mx-auto text-center">
    <h1 class="display-4 fw-normal">Pricing</h1>
    <p class="fs-5 text-muted">Choose the plan that fits your PDF generation needs.</p>
</div>

<div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
    {% for plan in plans %}
        <div class="col">
            <div class="card mb-4 rounded-3 shadow-sm {{ plan.name == 'Enterprise' ? 'border-primary' : '' }}">
                <div class="card-header py-3 {{ plan.name == 'Enterprise' ? 'text-bg-primary border-primary' : '' }}">
                    <h4 class="my-0 fw-normal">{{ plan.name }}</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">${{ plan.price }}<small class="text-muted fw-light">/mo</small></h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>{{ plan.limitGeneration }} PDFs / day</li>
                        <li>Email support</li>
                        <li>Help center access</li>
                    </ul>
                    <button type="button" class="w-100 btn btn-lg {{ plan.name == 'Enterprise' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Contact us
                    </button>
                    <!-- In a real app, this would submit a form to update the user's plan -->
                </div>
            </div>
        </div>
    {% else %}
        <div class="col-12">
            <p>No plans available.</p>
        </div>
    {% endfor %}
</div>
{% endblock %}
TWIG_EOF
