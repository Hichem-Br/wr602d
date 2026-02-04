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
