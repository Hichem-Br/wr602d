#!/bin/bash

# History Controller
docker exec -i symfony-web-v2 bash -c "cat > /var/www/src/Controller/HistoryController.php" <<'PHP_EOF'
<?php

namespace App\Controller;

use App\Repository\GenerationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history')]
    public function index(GenerationRepository $generationRepository): Response
    {
        $user = $this->getUser();
        $generations = $generationRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('history/index.html.twig', [
            'generations' => $generations,
        ]);
    }
}
PHP_EOF

# History Template
docker exec -w /var/www symfony-web-v2 mkdir -p templates/history
docker exec -i symfony-web-v2 bash -c "cat > /var/www/templates/history/index.html.twig" <<'TWIG_EOF'
{% extends 'base.html.twig' %}

{% block title %}History{% endblock %}

{% block body %}
<div class="container mt-4">
    <h1 class="mb-4">Generation History</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            {% if generations is empty %}
                <div class="alert alert-info">You haven't generated any PDFs yet.</div>
                <a href="{{ path('pdf_generation') }}" class="btn btn-primary">Generate your first PDF</a>
            {% else %}
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% for generation in generations %}
                                <tr>
                                    <td>{{ generation.createdAt ? generation.createdAt|date('Y-m-d H:i') : '' }}</td>
                                    <td>{{ generation.file }}</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                    <td>
                                        <!-- In a real app, we would store the file path and allow download -->
                                        <button class="btn btn-sm btn-outline-primary disabled">Download</button>
                                    </td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                </div>
            {% endif %}
        </div>
    </div>
</div>
{% endblock %}
TWIG_EOF
