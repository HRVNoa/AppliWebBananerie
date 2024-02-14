<?php

namespace App\Controller;

use App\Entity\Metier;
use App\Entity\Statut;
use App\Form\MetierModifierType;
use App\Form\MetierType;
use App\Form\StatutModifierType;
use App\Form\StatutType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatutController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('statut/index.html.twig', [
            'controller_name' => 'StatutController',
        ]);
    }
    public function consulterStatut(ManagerRegistry $doctrine, int $id){

        $statut= $doctrine->getRepository(Statut::class)->find($id);

        if (!$statut) {
            throw $this->createNotFoundException(
                'Aucun statut trouvé avec le numéro '.$id
            );
        }

        return $this->render('statut/consulter.html.twig', [
            'statut' => $statut,]);
    }
    public function listerStatut(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Statut::class);
        $statuts = $repository->findAll();

        return $this->render('statut/lister.html.twig', [
            'status' => $statuts,
        ]);
    }
    public function ajouterStatut(ManagerRegistry $doctrine,Request $request)
    {
        $statut = new statut();
        $form = $this->createForm(StatutType::class, $statut);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $statut = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($statut);
            $entityManager->flush();

            return $this->redirectToRoute('statutLister');
        } else {
            return $this->render('statut/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierStatut(ManagerRegistry $doctrine, $id, Request $request){

        $statut = $doctrine->getRepository(Statut::class)->find($id);

        if (!$statut) {
            throw $this->createNotFoundException('Aucun statut trouvé avec le numéro '.$id);
        }
        else
        {
            $form = $this->createForm(StatutModifierType::class, $statut);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $statut = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($statut);
                $entityManager->flush();
                return $this->redirectToRoute('statutLister');
            }
            else{
                return $this->render('statut/ajouter.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function supprimerStatut(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $statut = $entityManager->getRepository(Statut::class)->find($id);

        if (!$statut) {
            throw $this->createNotFoundException('Aucun statut trouvé avec le numéro '.$id);
        }
        $entityManager->remove($statut);
        $entityManager->flush();
        return $this->redirectToRoute('statutLister');
    }
}
