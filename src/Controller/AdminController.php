<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    public function listerNewMember(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(User::class);
        $newmembers = $repository->findAll();

        return $this->render('admin/confirmerUser.html.twig', [
            'newmembers' => $newmembers,
        ]);
    }
    public function confirmerNewMember(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $newmember = $entityManager->getRepository(User::class)->find($id);

        if (!$newmember) {
            throw $this->createNotFoundException('Aucun user trouvé avec le numéro '.$id);
        }
        $newmember->setConfirmed(true);

        $entityManager->persist($newmember);
        $entityManager->flush();

        return $this->redirectToRoute('newmemberLister');
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
        return $this->redirectToRoute('newmemberLister');
    }
}
