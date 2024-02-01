<?php

namespace App\Controller;

use App\Entity\Independant;
use App\Entity\Metier;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnuaireController extends AbstractController
{
//    #[Route('/annuaire', name: 'app_annuaire')]
    public function lister(ManagerRegistry $doctrine, Request $request): Response
    {
        $sort = $request->query->get('sort', 'rdm');

        if ($sort === "rdm"){
            $independants =$doctrine->getRepository(Independant::class)->findBy(["annuaire" => true]);
            shuffle($independants);
        }else{
            $independants = $doctrine
                ->getRepository(Independant::class)
                ->findAllSorted($sort);
        }

        $metiers =$doctrine->getRepository(Metier::class)->findAll();


        return $this->render('annuaire/lister.html.twig', [
            'independants' => $independants,
            'metiers' => $metiers,
        ]);
    }
}
