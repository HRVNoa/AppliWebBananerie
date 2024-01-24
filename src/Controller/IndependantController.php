<?php

namespace App\Controller;

use App\Entity\Independant;
use App\Form\IndependantModifierType;
use App\Form\IndependantType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndependantController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('independant/index.html.twig', [
            'controller_name' => 'IndependantController',
        ]);
    }
    public function consulterIndependant(ManagerRegistry $doctrine, int $id){

        $independant= $doctrine->getRepository(Independant::class)->find($id);

        if (!$independant) {
            throw $this->createNotFoundException(
                'Aucun independant trouvé avec le numéro '.$id
            );
        }

        return $this->render('independant/consulter.html.twig', [
            'independant' => $independant,]);
    }
    public function listerIndependant(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Independant::class);
        $independants = $repository->findAll();

        return $this->render('independant/lister.html.twig', [
            'independants' => $independants,
        ]);
    }
    public function ajouterIndependant(ManagerRegistry $doctrine,Request $request ,SessionInterface $session)
    {
        $independant = new independant();
        $form = $this->createForm(IndependantType::class, $independant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $independant = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($independant);
            $entityManager->flush();

            // Stockez l'ID de l'entreprise dans la session
            $session->set('independant_id', $independant->getId());

            // Redirigez vers le formulaire d'inscription de l'utilisateur
            return $this->redirectToRoute('app_register');
        } else {
            return $this->render('independant/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierIndependant(ManagerRegistry $doctrine, $id, Request $request){

        $independant = $doctrine->getRepository(Independant::class)->find($id);

        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        }
        else
        {
            $form = $this->createForm(IndependantModifierType::class, $independant);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $independant = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($independant);
                $entityManager->flush();
                return $this->render('independant/consulter.html.twig', ['independant' => $independant,]);
            }
            else{
                return $this->render('independant/ajouter.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function supprimerIndependant(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $independant = $entityManager->getRepository(Independant::class)->find($id);

        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        }
        $entityManager->remove($independant);
        $entityManager->flush();
        return $this->redirectToRoute('independantLister');
    }
}
