<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    public function index(Security $security): Response
    {
        if ($security->getUser()){
            return $this->render('index/index.html.twig', [
                'controller_name' => 'IndexController',
            ]);
        }
        return $this->redirectToRoute('app_login');
    }
    public function choix(): Response
    {
        return $this->render('index/choix.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
