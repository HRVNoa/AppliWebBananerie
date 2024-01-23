<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndependantController extends AbstractController
{
    #[Route('/independant', name: 'app_independant')]
    public function index(): Response
    {
        return $this->render('independant/index.html.twig', [
            'controller_name' => 'IndependantController',
        ]);
    }
}
