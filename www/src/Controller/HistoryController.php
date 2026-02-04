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
