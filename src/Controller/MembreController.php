<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MembreController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('membre/index.html.twig', [
            'controller_name' => 'MembreController',
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
}
