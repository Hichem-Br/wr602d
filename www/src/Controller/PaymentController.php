<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    /**
     * Crée une Checkout Session Stripe et redirige l'utilisateur vers Stripe.
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/checkout/{id}', name: 'app_payment_checkout')]
    public function checkout(
        Plan $plan,
        StripeService $stripeService,
        EntityManagerInterface $em,
    ): Response {
        // Le plan FREE ne nécessite pas de paiement
        if ($plan->getStripePriceId() === null) {
            $this->addFlash('info', 'Ce plan ne nécessite pas de paiement (gratuit ou non configuré).');
            return $this->redirectToRoute('app_subscription_change');
        }

        $successUrl = $this->generateUrl(
            'app_payment_success',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->generateUrl(
            'app_payment_cancel',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        try {
            $checkoutUrl = $stripeService->createCheckoutSession(
                $this->getUser(),
                $plan,
                $successUrl,
                $cancelUrl,
            );

            return $this->redirect($checkoutUrl);
        } catch (\Exception $e) {
            $this->addFlash('danger', "Erreur lors de l'initialisation du paiement Stripe : " . $e->getMessage());
            return $this->redirectToRoute('app_subscription_change');
        }
    }

    /**
     * Page affichée après un paiement réussi.
     * NE PAS mettre à jour le plan ici — c'est le rôle du webhook.
     */
    #[Route('/success', name: 'app_payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    /**
     * Page affichée si l'utilisateur annule le paiement.
     */
    #[Route('/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
