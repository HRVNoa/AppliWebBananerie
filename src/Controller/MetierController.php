<?php

namespace App\Controller;

use App\Entity\Metier;
use App\Form\MetierModifierType;
use App\Form\MetierType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MetierController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('metier/index.html.twig', [
            'controller_name' => 'MetierController',
        ]);
    }
    public function consulterMetier(ManagerRegistry $doctrine, int $id){

        $metier= $doctrine->getRepository(Metier::class)->find($id);

        if (!$metier) {
            throw $this->createNotFoundException(
                'Aucun metier trouvé avec le numéro '.$id
            );
        }

        return $this->render('metier/consulter.html.twig', [
            'metier' => $metier,]);
    }
    public function listerMetier(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Metier::class);
        $metiers = $repository->findAll();

        return $this->render('metier/lister.html.twig', [
            'metiers' => $metiers,
        ]);
    }
    public function ajouterMetier(ManagerRegistry $doctrine,Request $request)
    {
        $metier = new metier();
        $form = $this->createForm(MetierType::class, $metier);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $metier = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($metier);
            $entityManager->flush();

            return $this->redirectToRoute('metierLister');
        } else {
            return $this->render('metier/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierMetier(ManagerRegistry $doctrine, $id, Request $request){

        $metier = $doctrine->getRepository(Metier::class)->find($id);

        if (!$metier) {
            throw $this->createNotFoundException('Aucun metier trouvé avec le numéro '.$id);
        }
        else
        {
            $form = $this->createForm(MetierModifierType::class, $metier);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $metier = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($metier);
                $entityManager->flush();
                return $this->redirectToRoute('metierLister');
            }
            else{
                return $this->render('metier/ajouter.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function supprimerMetier(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $metier = $entityManager->getRepository(Metier::class)->find($id);

        if (!$metier) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        }
        $entityManager->remove($metier);
        $entityManager->flush();
        return $this->redirectToRoute('metierLister');
    }
}
