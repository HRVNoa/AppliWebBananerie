<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\Paiement;
use App\Entity\Tarif;
use App\Entity\User;
use App\Form\BourseAjouterType;
use App\Form\BourseModifierType;
use App\Form\PaiementType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BourseController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('bourse/index.html.twig', [
            'controller_name' => 'BourseController',
        ]);
    }

    //Fonction permettant de récupérer les données de la bourse (admin)
    public function getBourse(ManagerRegistry $doctrine, $userId): JsonResponse
    {
        //Récupération des données de la bourse
        $bourses = $doctrine->getRepository(Bourse::class)->findAll(['userId' => $userId]);
        $bourseArray = [];
        //Boucle permettant de récupérer l'id et la quantité pour les placements dans le fichier base.html.twig
        foreach ($bourses as $bourse) {
            if (method_exists($bourse, 'getId') && method_exists($bourse, 'getQuantite')) {
                $bourseArray[] = [
                    'id' => $bourse->getId(),
                    'text' => $bourse->getQuantite(),
                ];
            }
        }

        // Pour une réponse JSON
        return new JsonResponse($bourseArray);
    }

    //Fonction permettant de consulter une bourse (admin)
    public function consulterBourse(ManagerRegistry $doctrine, int $id){
        //Récupération des données
        $bourse = $doctrine->getRepository(Bourse::class)->find($id);

        //Si le membre n'est pas trouver
        if(!$bourse){
            throw $this->createNotFoundException('Aucune bourse ne correspond à cet identifiant : '.$id);
        }

        //Renvoie vers la vue
        return $this->render('bourse/consulter.html.twig', [
            'bourse' =>$bourse,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    //Fonction permettant de lister toute les bourses (admin)
    public function listerBourse(ManagerRegistry $doctrine){
        //Récupération des données
        $bourses = $doctrine->getRepository(Bourse::class)->findAll();

        //Renvoie vers la vue
        return $this->render('bourse/lister.html.twig', [
            'pBourses' => $bourses,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    //Fonction permettant d'ajouter une bourse (admin)
    public function ajouterBourse(ManagerRegistry $doctrine, Request $request){
        //Instantiation d'un bourse
        $bourse = new Bourse();

        //Appelle du formulaire
        $form = $this->createForm(BourseAjouterType::class, $bourse);
        $form->handleRequest($request);

        //Récupération des données
        $bourses = $doctrine->getRepository(Bourse::class)->findAll();

        if($form->isSubmitted() && $form->isValid()){
                $bourse = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($bourse);
                $entityManager->flush();

            return $this->redirectToRoute('bourseLister', [
               'pBourses' => $bourses,
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
        } else {
            return $this->render('bourse/ajouter.html.twig', array('form' => $form->createView()));
        }
    }

    //Fonction permettant de modifier un membre (Coté panel admin)
    public function ModifierBourse(ManagerRegistry $doctrine, Request $request, $id){
        //Récupération des données
        $bourse = $doctrine->getRepository(Bourse::class)->find($id);
        $bourses = $doctrine->getRepository(Bourse::class)->findAll();

        //Si aucune bourse n'est trouvé
        if(!$bourse){
            throw $this->createNotFoundException('Aucune bourse n\'as été trouvé avec cet identifiant : '.$id);
        } else {
            //Appelle du formulaire
            $form = $this->createForm(BourseModifierType::class, $bourse);
            $form->handleRequest($request);

            //Si le formulaire est envoyer et valide
            if($form->isSubmitted() && $form->isValid()){
                //Récupération des données du formulaire
                $bourse = $form->getData();

                //Envoie des données vers la base de données
                $entityManager = $doctrine->getManager();
                $entityManager->persist($bourse);
                $entityManager->flush();

                //Renvoie vers la vue
                return $this->redirectToRoute('bourseLister', [
                    'pBourses' => $bourses,
                    'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
                ]);
            } else {
                //Renvoie vers le formulaire si il n'est pas envoyer ou pas valide
                return $this->render('bourse/modifier.html.twig', array('form' => $form->createView()));
            }
        }
    }

    //Fonction permettant de supprimer une bourse
    public function supprimerBourse(EntityManagerInterface $entityManager, ManagerRegistry $doctrine, $id){
        //Récupération des données
        $bourse = $doctrine->getRepository(Bourse::class)->find($id);

        //Si aucun membre n'est trouver
        if(!$bourse){
            throw $this->createNotFoundException('Aucune Bourse n\'as été trouver avec cet identifiant'.$id);
        } else {

            $entityManager->remove($bourse);
            $entityManager->flush();
            $this->addFlash('success', 'L\'entité à été supprimée avec succès.');

            //Renvoie vers la route membreLister
            return $this->redirectToRoute('bourseLister', [
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
        }
    }
}
