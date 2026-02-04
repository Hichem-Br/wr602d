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
