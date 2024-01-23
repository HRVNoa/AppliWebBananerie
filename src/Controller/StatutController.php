<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatutController extends AbstractController
{
    #[Route('/statut', name: 'app_statut')]
    public function index(): Response
    {
        return $this->render('statut/index.html.twig', [
            'controller_name' => 'StatutController',
        ]);
    }
}
