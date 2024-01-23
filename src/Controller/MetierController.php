<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MetierController extends AbstractController
{
    #[Route('/metier', name: 'app_metier')]
    public function index(): Response
    {
        return $this->render('metier/index.html.twig', [
            'controller_name' => 'MetierController',
        ]);
    }
}
