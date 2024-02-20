<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Carrousel;
use App\Entity\Entreprise;
use App\Entity\Espace;
use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Media;
use App\Entity\Metier;
use App\Entity\Reservation;
use App\Entity\TarifEspaceTarif;
use App\Entity\User;
use App\Form\AdminReservationType;
use App\Form\EspaceAjouterType;
use App\Form\EspaceType;
use App\Form\MediaType;
use App\Form\MetierType;
use App\Form\RegistrationFormType;
use App\Form\TarifEspaceType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    </style>
</head>
<body>
    <div style=" max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;">
        <div style="background-color: #007bff;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            border-radius: 8px 8px 0 0;">
            Bienvenue sur l\'application de la bananarie!
        </div>
        <div style="padding: 20px;
            text-align: center;">
            <h1>Bonjour !</h1>
            <p>Votre inscription à l\'espace membre de la Bananerie a bien été prise en compte.</p>
            <p>Vous pouvez désormais vous connecter avec vos identifiants et réserver en ligne un espace, un studio ou encore consulter l’annuaire de nos membres.  .</p>
            <p>En cas de besoin, n’hésitez pas à nous contacter à icilabananerie@gmail.com</p>
            <p>A très bientôt à La Bananerie !</p>
            <p>
            LA BANANERIE<br>
            134 Bd Leroy<br>
            14 000 CAEN
            </p>
        </div>
        <div style=" background-color: #f4f4f4;
            color: #777777;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            border-radius: 0 0 8px 8px;">
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

    public function adminReservation(ManagerRegistry $doctrine)
    {
        $reservations = $doctrine->getRepository(Reservation::class)->findAll();


        return $this->render('admin/reservation.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    public function adminAnnuleReservation(Security $security, ManagerRegistry $doctrine, Request $request)
    {
        $id = $request->query->get('id', null);
        $reservation = $doctrine->getRepository(Reservation::class)->find($id);
        if ($reservation->getUser()->getIndependant() != null){
            $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
            $destName = $inde->getNom().' '.$inde->getPrenom();
            $mail = $inde->getEmail();
        }else if ($reservation->getUser()->getEntreprise() != null){
            $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
            $destName = $entr->getNom().' '.$entr->getPrenom();
            $mail = $entr->getEmail();
        }else{
            $destName = 'Admin';
            $mail = $security->getUser()->getUserIdentifier();
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($reservation);
        $entityManager->flush();

        // Envoi du mail pour confirmation
        $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
            ->to($mail)
            ->subject("Annulation d'une réservation : ". $reservation->getEspace()->getLibelle() ) // Sujet
            ->html('
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f4f4f4;
                    }
                </style>
                
                <div style="background-color: #ffffff;
                        width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                        color: #000;">
                <div style="background-color: #FACC5F;
                            color: #ffffff;
                            padding: 10px;
                            text-align: center;">
                    <h2>Annulation de votre réservation</h2>
                </div>
                <div style="padding: 20px;
                            text-align: left;">
                    <p>Bonjour '. $destName .',</p>
                    <p>Nous sommes désolés de vous informer que votre réservation a été annulée par un administrateur. Voici les détails de la réservation annulée :</p>
                    <ul>
                        <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                        <li>Heure : '. $reservation->getHeureDebut() .'h à '. $reservation->getHeureFin() .'h</li>
                        <li>Détail de la réservation : '. $reservation->getLibelle() .'</li>
                    </ul>
                    <p>Pour plus d\'informations ou pour toute question, n\'hésitez pas à nous contacter La Bananerie ou via l\'espace membre.</p>
                    <p>Nous espérons avoir l\'opportunité de vous accueillir prochainement.</p>
                    <p>Cordialement,</p>
                    <p>La Bananerie.</p>
                </div>
                <div style="background-color: #333333;
                            color: #ffffff;
                            padding: 10px;
                            text-align: center;
                            font-size: 12px;">
                    Merci de votre compréhension | <a href="https://google.fr" style="color: #ffffff;">Visitez notre site</a>
                </div>
            </div>
            ');
        try {
            $mailer->send($email);
            $this->addFlash('success', 'L\'utilisateur a été notifier par mail ('. $mail .').');

        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', "Oops! Quelque chose s'est mal passé et utilisateur n'a été notifier par email. Cause : ".$e);
        }
        return $this->redirectToRoute('adminReservation');
    }

    public function adminAjoutReservation(Request $request, ManagerRegistry $doctrine, Security $security)
    {

        $reservation = new Reservation();
        $form = $this->createForm(AdminReservationType::class, $reservation);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $plageHoraire = $form->get('plageHoraire')->getData();

            switch ($plageHoraire) {
                case 'journee':
                    $heureDebut = new \DateTime('09:00');
                    $heureFin = new \DateTime('18:00');
                    break;
                case 'matin':
                    $heureDebut = new \DateTime('09:00');
                    $heureFin = new \DateTime('13:00');
                    break;
                case 'apres-midi':
                    $heureDebut = new \DateTime('14:00');
                    $heureFin = new \DateTime('18:00');
                    break;
            }

            if ($plageHoraire) {
                $reservation->setHeureDebut($heureDebut);
                $reservation->setHeureFin($heureFin);
            }

            if ($reservation->getUser()->getIndependant() != null){
                $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
                $destName = $inde->getNom().' '.$inde->getPrenom();
                $mail = $inde->getEmail();
            }else if ($reservation->getUser()->getEntreprise() != null){
                $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                $destName = $entr->getNom().' '.$entr->getPrenom();
                $mail = $entr->getEmail();
            }else{
                $destName = 'Admin';
                $mail = $security->getUser()->getUserIdentifier();
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Envoi du mail pour confirmation
            $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
                ->to($reservation->getUser()->getUserIdentifier())
                ->subject("Nouvelle réservation : ". $reservation->getEspace()->getLibelle() ) // Sujet
                ->html('
                <div style="background-color: #ffffff;
                    width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    color: #000;">
                <div style="background-color: #FACC5F;
                    color: #ffffff;
                    padding: 10px;
                    text-align: center;">
                    <h2>ANNULATION DE RÉSERVATION</h2>
                </div>
                <div style="padding: 20px;
                    text-align: left;">
                    <p>Bonjour '. $destName .',</p>
                    <p>L\'administraeur a annulé une réservation.</p>
                    <p>Détails de réservation:</p>
                    <ul>
                        <li>Espace : '. $reservation->getEspace()->getLibelle() .'</li>
                        <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                        <li>Heure : '. $reservation->getHeureDebut() .'h à '. $reservation->getHeureFin() .'h</li>
                        <li>Detail de votre réservation : '.$reservation->getLibelle().'</li>
                    </ul>
                    <p>Un remboursement de X B. COINS sera effectué sur votre compte espace membre. </p>
                    <p>À très bientôt à La Bananerie ! </p>
                </div>
                <div style="
                    background-color: #333333;
                    color: #ffffff;
                    padding: 10px;
                    text-align: center;
                    font-size: 12px;">
                    <p>LA BANANERIE</p>
                    <p>134 Bd Leroy</p>
                    <p>14 000 CAEN</p>
                </div>
            </div>
            ');
            try {
                $mailer->send($email);
                $this->addFlash('success', 'L\'utilisateur a été notifier par mail ('. $mail .').');

            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', "Oops! Quelque chose s'est mal passé et utilisateur n'a été notifier par email. Cause : ".$e);
            }

            // Rediriger vers la page appropriée
            return $this->redirectToRoute('adminReservation');
        } else {
            return $this->render('admin/formReservation.html.twig', array('form' => $form,));
        }
    }

    public function adminEspaceAjouter(ManagerRegistry $doctrine,Request $request)
    {
        $espace = new Espace();
        $form = $this->createForm(EspaceAjouterType::class, $espace);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $dureeTarif = $form->get('dureeTarif')->getData();
                $carrousel = new Carrousel();
                $espace->setCarrousel($carrousel);
                switch ($dureeTarif) {
                    case '1_4_9':
                        $prix = new TarifEspaceTarif();
                        $prix->setPrix(0);
                        $prix->setHeure(1);
                        $prix->setEspace($espace);
                        $espace->addTarifEspaceTarif($prix);
                        $prix2 = new TarifEspaceTarif();
                        $prix2->setPrix(0);
                        $prix2->setHeure(4);
                        $prix2->setEspace($espace);
                        $espace->addTarifEspaceTarif($prix2);
                        $prix3 = new TarifEspaceTarif();
                        $prix3->setPrix(0);
                        $prix3->setHeure(9);
                        $prix3->setEspace($espace);
                        $espace->addTarifEspaceTarif($prix3);
                    case '4_9':
                        $prix = new TarifEspaceTarif();
                        $prix->setPrix(0);
                        $prix->setHeure(4);
                        $prix->setEspace($espace);
                        $espace->addTarifEspaceTarif($prix);
                        $prix2 = new TarifEspaceTarif();
                        $prix2->setPrix(0);
                        $prix2->setHeure(9);
                        $prix2->setEspace($espace);
                        $espace->addTarifEspaceTarif($prix2);
                    case 'none':
                }
                $espace = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($carrousel);
                $entityManager->persist($espace);
                $entityManager->flush();
                $this->addFlash('success', 'L\'espace a été créer avec succès.');

            }catch (\Exception $e){
                $this->addFlash('error', 'L\'espace n\'a pas pu être créer.'.$e);

            }

            return $this->redirectToRoute('adminEspace');
        } else {
            return $this->render('admin/formEspaceAjouter.html.twig', array('form' => $form->createView(),));
        }
    }
    public function adminEspace(ManagerRegistry $doctrine)
    {
        $espaces = $doctrine->getRepository(Espace::class)->findAll();
        return $this->render('admin/espace.html.twig', ['espaces' => $espaces,]);
    }

    public function adminEspaceSupprimer(ManagerRegistry $doctrine, int $id)
    {
        $entityManager = $doctrine->getManager();
        $espace = $entityManager->getRepository(Espace::class)->find($id);

        if (!$espace) {
            throw $this->createNotFoundException('Aucun espace trouvé avec le numéro '.$id);
        }
        try {
            $entityManager->remove($espace);
            $entityManager->flush();
            $this->addFlash('success', 'L\'espace a été supprimer avec succès.');

        }catch (\Exception $e){
            $this->addFlash('error', 'L\'espace n\'a pas pu être supprimer.'.$e);

        }
        return $this->redirectToRoute('adminEspace');

    }

    public function adminEspaceModif(ManagerRegistry $doctrine, Request $request, $id)
    {
        $espace = $doctrine->getRepository(Espace::class)->find($id);
        $form = $this->createForm(EspaceType::class, $espace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($espace);
                $entityManager->flush();
                $this->addFlash('success', 'L\'espace a été mis à jour avec succès.');

                return $this->redirectToRoute('adminEspace');

            } catch (\Exception $e){
                $this->addFlash('error', 'L\'espace n\'a pas pu être mis à jour. Cause : ' . $e);
                return $this->redirectToRoute('adminEspace');
            }
        }else{
            return $this->render('admin/formEspace.html.twig', ['form' => $form,]);
        }

    }

    public function adminEspaceCarrousel(ManagerRegistry $doctrine, $id)
    {
        $espace = $doctrine->getRepository(Espace::class)->find($id);
        $medias = $espace->getCarrousel()->getMedia();
        return $this->render('admin/mediaEspace.html.twig', ['medias' => $medias,'espace' => $espace->getLibelle(), 'carrousel' => $espace->getCarrousel()]);
    }

    public function adminEspaceCarrouselAdd(ManagerRegistry $doctrine, Request $request, $id)
    {
        $media = new Media();
        if ($id != null) {
            $espace = $doctrine->getRepository(Espace::class)->find($id);
            $media->setCarrousel($espace->getCarrousel());
        }
        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['lien']->getData();
            if ($file instanceof UploadedFile) {

                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // récupère le nom de l'image uploaded
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); // Raison de sécurité mettre les caratères en lower et enlever les autres caratères qui n'est pas des lettres et des chiffres
                $newFilename = $safeFilename . '.' . $file->guessExtension(); // Nommage de l'image dans le dossier avec l'extension qui convient

                // Dépose l'image dans le répertoire média
                try {
                    $file->move(
                        $this->getParameter('MediaEspace') .'/'.$media->getCarrousel()->getId().'/',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'La photo n\'a pas pu être ajoutée. Cause : ' . $e);
                }

                // Défini l'image dans le champs lien de l'entité média
                $media->setLien($newFilename);
            }
            try {
                $entityManager = $doctrine->getManager();
                $entityManager->persist($media);
                $entityManager->flush();
                $this->addFlash('success', 'La photo a été ajoutée avec succès.');

                return $this->redirectToRoute('adminEspaceCarrousel',['id' => $espace->getCarrousel()->getId()]);

            } catch (\Exception $e){
                $this->addFlash('error', 'La photo n\'a pas pu être ajoutée. Cause : ' . $e);
                return $this->redirectToRoute('adminEspaceCarrousel',['id' => $espace->getCarrousel()->getId()]);
            }
        }else{
            return $this->render('admin/formMediaEspace.html.twig', ['form' => $form, 'carrousel' => $espace->getCarrousel(), 'id' => $id]);
        }
    }

    public function adminEspaceCarrouselDel(ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();
        $media = $entityManager->getRepository(Media::class)->find($id);

        if (!$media) {
            $this->addFlash('error', 'Aucun média trouvé.');
        }

        // supprime l'ancienne image
        $oldFilePath = $this->getParameter('MediaEspace') . '/' . $media->getCarrousel()->getId() . '/' . $media->getLien();
        if ($media->getLien() && file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        $entityManager->remove($media);
        $entityManager->flush();

        $this->addFlash('success', 'Média a bien été supprimé.');
        return $this->redirectToRoute('adminEspaceCarrousel',['id' => $media->getCarrousel()->getEspace()->getId()]);

    }

    public function adminEspacePrixAdd(ManagerRegistry $doctrine, Request $request, $id){
        $tarif = $doctrine->getRepository(TarifEspaceTarif::class)->find($id);
        $form = $this->createForm(TarifEspaceType::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            try {
                $data = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($data);
                $entityManager->flush();
                $this->addFlash('success', 'Le prix a bien été ajouter.');
            }catch (\Exception $e){
                $this->addFlash('error', 'Le prix n\'a pas été ajouter. Cause : '.$e);
            }
            return $this->redirectToRoute('adminEspace');
        }else{
            return $this->render('admin/formPrixEspace.html.twig', [
                'form' => $form,
                'tarif' => $tarif,
            ]);
        }
    }


    public function adminEspacePrix(ManagerRegistry $doctrine, Request $request, $id)
    {
        $tarif = $doctrine->getRepository(TarifEspaceTarif::class)->find($id);
        $form = $this->createForm(TarifEspaceType::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            try {
                $data = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($data);
                $entityManager->flush();
                $this->addFlash('success', 'Le prix a bien été mis à jour.');
            }catch (\Exception $e){
                $this->addFlash('error', 'Le prix n\'a pas été mis à jour. Cause : '.$e);
            }
            return $this->redirectToRoute('adminEspace');
        }else{
            return $this->render('admin/formPrixEspace.html.twig', [
                'form' => $form,
                'tarif' => $tarif,
            ]);
        }
    }
    public function ajouterAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ManagerRegistry $doctrine ,SessionInterface $session): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );


            $user->setRoles(["ROLE_ADMIN"]);
            $bourse = new Bourse();
            $bourse->setQuantite(0);
            $user->setBourse($bourse);
            $user->setConfirmed(1);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('adminLister');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    public function listerAdmin(ManagerRegistry $doctrine)
    {
        $repository = $doctrine->getRepository(User::class);
        $users = $repository->findAll();
        $admins =[];
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $admins[] = $user;
            }
        }
        return $this->render('admin/lister.html.twig', [
            'admins' => $admins,
        ]);
    }
    public function supprimerAdmin(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $admin = $entityManager->getRepository(User::class)->find($id);

        if (!$admin) {
            throw $this->createNotFoundException('Aucun admin trouvé avec le numéro '.$id);
        }
        $entityManager->remove($admin);
        $entityManager->flush();
        return $this->redirectToRoute('adminSupprimer');
    }

    public function contactMousquetaires(ManagerRegistry $doctrine): Response
    {
        return $this->render('admin/contactMousquetaires.html.twig', [ 'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
}
