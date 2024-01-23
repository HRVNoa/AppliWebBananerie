<?php

namespace App\Controller;


use App\Entity\SecteurActivite;
use App\Form\SecteurActiviteModifierType;
use App\Form\SecteurActiviteType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecteurActiviteController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('secteur/index.html.twig', [
            'controller_name' => 'SecteurActiviteController',
        ]);
    }
    public function consulterSecteurActivite(ManagerRegistry $doctrine, int $id){

        $secteurActivite= $doctrine->getRepository(SecteurActivite::class)->find($id);

        if (!$secteurActivite) {
            throw $this->createNotFoundException(
                'Aucun secteur trouvé avec le numéro '.$id
            );
        }

        return $this->render('secteur/consulter.html.twig', [
            'secteur' => $secteurActivite,]);
    }
    public function listerSecteurActivite(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(SecteurActivite::class);
        $secteurActivites = $repository->findAll();

        return $this->render('secteur/lister.html.twig', [
            'secteurs' => $secteurActivites,
        ]);
    }
    public function ajouterSecteurActivite(ManagerRegistry $doctrine,Request $request)
    {
        $secteurActivite = new secteuractivite();
        $form = $this->createForm(SecteurActiviteType::class, $secteurActivite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $secteurActivite = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($secteurActivite);
            $entityManager->flush();

            return $this->render('secteur/consulter.html.twig', ['secteur' => $secteurActivite,]);
        } else {
            return $this->render('secteur/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierSecteurActivite(ManagerRegistry $doctrine, $id, Request $request){

        $secteurActivite = $doctrine->getRepository(SecteurActivite::class)->find($id);

        if (!$secteurActivite) {
            throw $this->createNotFoundException('Aucun secteur trouvé avec le numéro '.$id);
        }
        else
        {
            $form = $this->createForm(SecteurActiviteModifierType::class, $secteurActivite);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $secteurActivite = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($secteurActivite);
                $entityManager->flush();
                return $this->render('secteur/consulter.html.twig', ['secteur' => $secteurActivite,]);
            }
            else{
                return $this->render('secteur/ajouter.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function supprimerSecteurActivite(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $secteurActivite = $entityManager->getRepository(SecteurActivite::class)->find($id);

        if (!$secteurActivite) {
            throw $this->createNotFoundException('Aucun secteur trouvé avec le numéro '.$id);
        }
        $entityManager->remove($secteurActivite);
        $entityManager->flush();
        return $this->redirectToRoute('secteurLister');
    }
}
