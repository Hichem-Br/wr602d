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
