<?php

namespace App\Controller;

use App\Entity\Independant;
use App\Form\ContactType;
use App\Form\IndependantModifierType;
use App\Form\IndependantType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class IndependantController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('independant/index.html.twig', [
            'controller_name' => 'IndependantController',
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
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
            'independant' => $independant,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function listerIndependant(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(Independant::class);
        $independants = $repository->findAll();

        return $this->render('independant/lister.html.twig', [
            'independants' => $independants,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
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
            return $this->redirectToRoute('app_register', [
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
        } else {
            return $this->render('independant/ajouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function modifierIndependant(ManagerRegistry $doctrine, $id, Request $request)
    {
        $independant = $doctrine->getRepository(Independant::class)->find($id);

        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        } else {
            // Récupérer les tags actuellement sélectionnés par l'indépendant
            $selectedTags = [];
            foreach ($independant->getIndependantTags() as $independantTag) {
                $selectedTags[] = $independantTag->getTag();
            }

            // Créer le formulaire avec les tags sélectionnés
            $form = $this->createForm(IndependantModifierType::class, $independant, [
                'tags' => $selectedTags,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $independant = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($independant);
                $entityManager->flush();
                return $this->render('independant/consulter.html.twig', [
                    'independant' => $independant,
                    'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
                ]);
            } else {
                return $this->render('independant/ajouter.html.twig', ['form' => $form->createView()]);
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
        return $this->redirectToRoute('independantLister', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function sendmailIndependant(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from(new Address('bananeriebot@gmail.com', 'Contact La Bananerie'))
                ->to("icilabananerie@gmail.com")
                ->subject("Nouveau message de " . $formData['nom']) // Sujet
//                ->text('ici le texte');
                ->text(
                    "Nom: " . $formData['nom'] . "\n" .
                    "Email: " . $formData['email'] . "\n" .
                    "Téléphone: " . $formData['phone'] . "\n\n" .
                    "Objet: " . $formData['objet'] . "\n\n" .
                    "Message:\n\n" . $formData['message']
                );
            try {
                $mailer->send($email);
                $this->addFlash('error', 'Merci! Votre message a été envoyé.');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', "Oops! Quelque chose s'est mal passé et nous n'avons pas pu envoyer votre message.");
            }

        }

        return $this->render('contact/contact.html.twig', [
            'page_name' => 'Contact',
            'form' => $form->createView(),
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
}
