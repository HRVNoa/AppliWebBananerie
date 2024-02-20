<?php

namespace App\Controller;

use App\Entity\Metier;
use App\Entity\Tag;
use App\Form\MetierModifierType;
use App\Form\MetierType;
use App\Form\TagModifierType;
use App\Form\TagType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('tag/index.html.twig', [
            'controller_name' => 'TagController',
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function consulterTag(ManagerRegistry $doctrine, int $id){

        $tag= $doctrine->getRepository(Tag::class)->find($id);

        if (!$tag) {
            throw $this->createNotFoundException(
                'Aucun tag trouvé avec le numéro '.$id
            );
        }

        return $this->render('tag/consulter.html.twig', [
            'tag' => $tag,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function listerTag(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Tag::class);
        $tags = $repository->findAll();

        return $this->render('tag/lister.html.twig', [
            'tags' => $tags,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function ajouterTag(ManagerRegistry $doctrine,Request $request)
    {
        $tag = new tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $tag = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();

            return $this->redirectToRoute('tagLister', [
                'tag' => $tag,
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
        } else {
            return $this->render('tag/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierTag(ManagerRegistry $doctrine, $id, Request $request){

        $tag = $doctrine->getRepository(Tag::class)->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Aucun tag trouvé avec le numéro '.$id);
        }
        else
        {
            $form = $this->createForm(TagModifierType::class, $tag);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $tag = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($tag);
                $entityManager->flush();

                return $this->redirectToRoute('tagLister', [
                    'tag' => $tag,
                    'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
                ]);

            }
            else{
                return $this->render('tag/ajouter.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function supprimerTag(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $tag = $entityManager->getRepository(Tag::class)->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Aucun tag trouvé avec le numéro '.$id);
        }
        $entityManager->remove($tag);
        $entityManager->flush();
        return $this->redirectToRoute('tagLister', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
}
