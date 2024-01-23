<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Form\EntrepriseModifierType;
use App\Form\EntrepriseType;
use App\Form\IndependantModifierType;
use App\Form\IndependantType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('entreprise/index.html.twig', [
            'controller_name' => 'EntrepriseController',
        ]);
    }
    public function consulterEntreprise(ManagerRegistry $doctrine, int $id){

        $entreprise= $doctrine->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            throw $this->createNotFoundException(
                'Aucun entreprise trouvé avec le numéro '.$id
            );
        }

        return $this->render('entreprise/consulter.html.twig', [
            'entreprise' => $entreprise,]);
    }
    public function listerEntreprise(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Entreprise::class);
        $entreprises = $repository->findAll();

        return $this->render('entreprise/lister.html.twig', [
            'entreprises' => $entreprises,
        ]);
    }
    public function ajouterEntreprise(ManagerRegistry $doctrine,Request $request)
    {
        $entreprise = new entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entreprise = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($entreprise);
            $entityManager->flush();

            return $this->render('entreprise/consulter.html.twig', ['entreprise' => $entreprise,]);
        } else {
            return $this->render('entreprise/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierEntreprise(ManagerRegistry $doctrine, $id, Request $request){

        $entreprise = $doctrine->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            throw $this->createNotFoundException('Aucun entreprise trouvé avec le numéro '.$id);
        }
        else
        {
            $form = $this->createForm(EntrepriseModifierType::class, $entreprise);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $entreprise = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($entreprise);
                $entityManager->flush();
                return $this->render('entreprise/consulter.html.twig', ['entreprise' => $entreprise,]);
            }
            else{
                return $this->render('entreprise/ajouter.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function supprimerEntreprise(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $entreprise = $entityManager->getRepository(Entreprise::class)->find($id);

        if (!$entreprise) {
            throw $this->createNotFoundException('Aucun entreprise trouvé avec le numéro '.$id);
        }
        $entityManager->remove($entreprise);
        $entityManager->flush();
        return $this->redirectToRoute('entrepriseLister');
    }
}