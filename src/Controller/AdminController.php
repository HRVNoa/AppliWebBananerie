<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Metier;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function listerNewMember(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(User::class);
        $newmembers = $repository->findAll();

        return $this->render('admin/confirmerUser.html.twig', [
            'newmembers' => $newmembers,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function confirmerNewMember(ManagerRegistry $doctrine, int $id): Response
    {
        $bourse = new Bourse();
        $entityManager = $doctrine->getManager();
        $newmember = $entityManager->getRepository(User::class)->find($id);

        if (!$newmember) {
            throw $this->createNotFoundException('Aucun user trouvé avec le numéro '.$id);
        }
        $newmember->setConfirmed(true);

        $bourse->setUser($newmember);
        $bourse->setQuantite(0);

        $entityManager->persist($bourse);
        $entityManager->persist($newmember);
        $entityManager->flush();

        return $this->redirectToRoute('newmemberLister', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function refuserNewMember(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $newmember = $entityManager->getRepository(User::class)->find($id);
        $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $id]);
        $entreprise = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $id]);
        if ($inde) {
            $idinde = $inde->getId();
        } else {
            $idinde = null;
        }
        if ($entreprise) {
            $idente = $entreprise->getId();
        } else {
            $idente = null;
        }
        $indetag = $doctrine->getRepository(IndependantTag::class)->findBy(['independant' => $idinde]);
        if ($inde == null) {
            foreach ($entreprise as $entre) {
                $entityManager->remove($entre);
            }
        }
        else {
            foreach ($inde as $independant) {
                $entityManager->remove($independant);
            }

            foreach ($indetag as $tag) {
                $entityManager->remove($tag);
            }
        }
        if (!$newmember) {
            throw $this->createNotFoundException('Aucun newmember trouvé avec le numéro '.$id);
        }
        $entityManager->remove($newmember);
        $entityManager->flush();
        return $this->redirectToRoute('newmemberLister', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function annuaireControl(ManagerRegistry $doctrine, Request $request)
    {
        $sort = $request->query->get('sort', 'asc');

        $independants = $doctrine
            ->getRepository(Independant::class)
            ->findAllSorted($sort);

        return $this->render('admin/annuaire.html.twig', [
            'independants' => $independants,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function delIndependantAnnuaire(ManagerRegistry $doctrine, $id)
    {
        //récupération de l'annuaire avec son id
        $independant = $doctrine->getRepository(Independant::class)->find($id);

        if (!$independant) { // test si il y a une categorie lier avec l'id
            $this->addFlash('error', 'L\'independant que vous voulais supprimé n\'existe pas.');
            return $this->redirectToRoute('annuaireControl');
        }
        $independant->setAnnuaire(false);
        try {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($independant);
            $entityManager->flush();
            $this->addFlash('success', 'L\'independant n\'est plus afficher dans l\'annuaire avec succès.');

        } catch (\Exception $e){
            $this->addFlash('error', 'L\'independant n\'a pas pu être retirer. Cause : ' . $e);
        }
        return $this->redirectToRoute('annuaireControl', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

}
