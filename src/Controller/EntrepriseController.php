<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\User;
use App\Form\EntrepriseModifierType;
use App\Form\EntrepriseType;
use App\Form\IndependantModifierType;
use App\Form\IndependantType;
use App\Form\RegistrationFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('entreprise/index.html.twig', [
            'controller_name' => 'EntrepriseController',
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
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
            'entreprise' => $entreprise,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function listerEntreprise(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Entreprise::class);
        $entreprises = $repository->findAll();

        return $this->render('/entreprise/lister.html.twig', [
            'entreprises' => $entreprises,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function ajouterEntreprise(ManagerRegistry $doctrine,Request $request ,SessionInterface $session)
    {
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        $user = new User();
        $form2 = $this->createForm(RegistrationFormType::class, $user);
        $form2->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entreprise = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($entreprise);
            $entityManager->flush();

            // Stockez l'ID de l'entreprise dans la session
            $session->set('entreprise_id', $entreprise->getId());
            $session->set('user_email', $entreprise->getEmail());

            // Redirigez vers le formulaire d'inscription de l'utilisateur
            return $this->redirectToRoute('app_register', [
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
        } else {
            return $this->render('entreprise/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierEntreprise(ManagerRegistry $doctrine, $id, Request $request, Security $security){

        $entreprise = $doctrine->getRepository(Entreprise::class)->find($id);
        $user = $entreprise->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'une autre entreprise.');
            return $this->redirectToRoute('accueilIndex');
        }

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
                return $this->render('entreprise/consulter.html.twig', [
                    'entreprise' => $entreprise,
                    'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
                ]);
            }
            else{
                return $this->render('entreprise/formModif.html.twig',['entreprise' => $entreprise, 'form' => $form->createView()]);
            }
        }
    }
    public function supprimerEntreprise(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $entreprise = $entityManager->getRepository(Entreprise::class)->find($id);
        $entreprise->setUser(null);
        if (!$entreprise) {
            throw $this->createNotFoundException('Aucun entreprise trouvé avec le numéro '.$id);
        }
        //$entityManager->remove($bourse);
        $entityManager->remove($entreprise);
        $entityManager->flush();
        return $this->redirectToRoute('entrepriseLister', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
}
