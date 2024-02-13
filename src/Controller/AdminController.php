<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Metier;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
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

        $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from(new Address('bananeriebot@gmail.com', 'Contact La Bananerie'))
            ->to($newmember->getUserIdentifier())
            ->subject("Bienvenue à La Bananerie") // Sujet
            ->html('
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .footer {
            background-color: #f4f4f4;
            color: #777777;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }
        a.button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Bienvenue sur l\'application de la bananarie!
        </div>
        <div class="content">
            <h1>Bonjour !</h1>
            <p>Nous sommes ravis de vous compter parmi nous.</p>
            <p>Vous faites maintenant partie de notre communauté grandissante. Préparez-vous à découvrir toutes les possibilités offertes par nos services.</p>
            <p>Nous vous invitons à visiter notre site web:</p>
            <a href="https://www.votreentreprise.com" class="button">Visitez notre application</a>
        </div>
        <div class="footer">
            Merci de nous avoir rejoint. Si vous avez des questions, n\'hésitez pas à contacter notre support.
            <br>Email: icilabananerie@gmail.com
    </div>
    </div>
</body>
</html>
    ');
        try {
            $mailer->send($email);
            $this->addFlash('succesmail', 'Votre confirmation a été envoyé.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('errormail', "Oops! Quelque chose s'est mal passé et nous n'avons pas pu envoyer votre message.");
        }



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

    public function annuaireControl(ManagerRegistry $doctrine, Request $request)
    {
        $sort = $request->query->get('sort', 'asc');

        $independants = $doctrine
            ->getRepository(Independant::class)
            ->findAllSorted($sort);

        return $this->render('admin/annuaire.html.twig', [
            'independants' => $independants,
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
        return $this->redirectToRoute('annuaireControl');
    }

}
