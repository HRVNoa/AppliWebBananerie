<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Remboursement;
use App\Entity\User;
use App\Form\ReservationHeureType;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Entreprise;
use App\Entity\Espace;
use App\Entity\Independant;
use App\Entity\Media;
use App\Entity\Reservation;
use App\Form\ReservationType;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use function Symfony\Component\String\s;

class IndexController extends AbstractController
{
    public function index(Security $security, ManagerRegistry $doctrine, Request $request): Response
    {
        if ($security->getUser()){

            $userReservations = $doctrine->getRepository(Reservation::class)->getReservationFutureByUser($security->getUser());

            $jour = $request->query->get('jour', date("d/m/Y"));
            $jour = DateTime::createFromFormat('d/m/Y', $jour);
            if ($jour <= new DateTime()){
                $jour = new DateTime();
            }
            $espaces = $doctrine->getRepository(Espace::class)->findAll();
            $reservations = $doctrine->getRepository(Reservation::class)->findBy(['date' => $jour]);

            return $this->render('index/index.html.twig', [
                'controller_name' => 'IndexController',
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
                'espaces' => $espaces,
                'reservations' => $reservations,
                'userReservations' => $userReservations,
                'jour' => $jour->format('d/m/Y'),
            ]);
        }
        return $this->redirectToRoute('app_login', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function modalDetail(Request $request, ManagerRegistry $doctrine, Security $security)
    {
        if ($request->query->get('id', null) != null){
            $id = $request->query->get('id', null);
            $userReservation = $request->query->get('userReservation', null);
            $reservation = $doctrine->getRepository(Reservation::class)->find($id);
            if (!$reservation) {
                $this->addFlash('error', 'Aucune réservation trouvé avec le numéro '.$id);
                return $this->render('index/modalDetail.html.twig', [
                    'succes' => false,
                    'annulable' => false,
                ]);
            } else {
                $now = new DateTime('now');
                if ($reservation->getDate()->modify("-2 day") < $now){
                    $annulable = false;
                }else{
                    $annulable = true;
                }
                $reservation->getDate()->modify("+2 day");

                if ($request->query->get('annuler', false)) {
                    try {
                        if ($annulable){
                            //Annulation de la reservation

                            if ($reservation->getUser()->getIndependant() != null){
                                $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
                                $destName = $inde->getNom().' '.$inde->getPrenom();
                                $user = $inde;
                            }else if ($reservation->getUser()->getEntreprise() != null){
                                $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                                $destName = $entr->getNom().' '.$entr->getPrenom();
                                $user = $entr;
                            }else{
                                $destName = 'Admin';
                                $user = 'Admin';
                            }

                            $entityManager = $doctrine->getManager();
                            $entityManager->remove($reservation);
                            $entityManager->flush();

                            $this->addFlash('success', 'Merci ! La réservation a bien été annulée');

                            //Rembourssement de B.coins

                            try {
                                if ($user != 'Admin'){
                                    $nbHeure = $reservation->getHeureFin() - $reservation->getHeureDebut();
                                    if ($nbHeure < 4){
                                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                            if ($tarifs->getHeure() == 1){
                                                $prix = $tarifs->getPrix() * $nbHeure;
                                            }
                                        }
                                    }elseif ($nbHeure < 9){
                                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                            if ($tarifs->getHeure() == 4){
                                                $prix = $tarifs->getPrix();
                                            }
                                        }
                                        $nbHeure = $nbHeure - 4;
                                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                            if ($tarifs->getHeure() == 1){
                                                $prix = $tarifs->getPrix() * $nbHeure + $prix;
                                            }
                                        }
                                    }elseif ($nbHeure = 9){
                                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                            if ($tarifs->getHeure() == 9){
                                                $prix = $tarifs->getPrix();
                                            }
                                        }
                                    }
                                    $bourse = $doctrine->getRepository(Bourse::class)->find($reservation->getUser()->getBourse());
                                    $solde = $reservation->getUser()->getBourse()->getQuantite();
                                    $reservation->getUser()->getBourse()->setQuantite($solde+$prix);

                                    $entityManager->persist($bourse);
                                    $entityManager->flush();

                                }

                                $this->addFlash('success', 'Vous avez bien été rembourser');
                            }catch (Exception $e){
                                $this->addFlash('error', 'Oops! Quelque chose s\'est mal passé et nous avons pas pu vous rembourser.');
                                $this->addFlash('error', 'La Bananerie a été notifié de cette erreur. Contactez La Bananerie pour confirmer votre non remboursement');
                            }


                            //Ajoute en base dans la table remboursement
                            $remboursement = new Remboursement();
                            $remboursement->setQuantite($reservation->getQuantite());
                            $remboursement->setUser($reservation->getUser());
                            $remboursement->setLibelle($reservation->getLibelle());
                            $remboursement->setEspace($reservation->getEspace());
                            $remboursement->setHeureDebut($reservation->getHeureDebut());
                            $remboursement->setHeureFin($reservation->getHeureFin());
                            $remboursement->setDate($reservation->getDate());

                            $entityManager->persist($remboursement);
                            $entityManager->flush();

                            // Envoi du mail pour confirmation
                            $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
                            $mailer = new Mailer($transport);
                            $email = (new Email())
                                ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
                                ->to($security->getUser()->getUserIdentifier())
                                ->subject("Annulation d'une réservation : ". $reservation->getEspace()->getLibelle() ) // Sujet
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
                                        <p>L’annulation de votre réservation à La Bananerie a bien été prise en compte.</p>
                                        <p>Détails de réservation:</p>
                                        <ul>
                                            <li>Espace : '. $reservation->getEspace()->getLibelle() .'</li>
                                            <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                                            <li>Heure : '. $reservation->getHeureDebut() .'h à '. $reservation->getHeureFin() .'h</li>
                                            <li>Detail de votre réservation : '.$reservation->getLibelle().'</li>
                                        </ul>
                                        <p>Un remboursement de '. $prix .' B. COINS sera effectué sur votre compte espace membre. </p>
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
                                $this->addFlash('success', 'Une confirmation vous a été envoyé par mail.');
                            } catch (TransportExceptionInterface $e) {
                                $this->addFlash('error', "Oops! Quelque chose s'est mal passé et nous n'avons pas pu envoyer un accusé de la réservation par email.");
                            }

                            return $this->render('index/reservation.html.twig', [
                                'reservation' => $reservation,
                                'succes' => false
                            ]);
                        }else{
                            $this->addFlash('error', 'La réservation ne peut être annulée seulement 48h à l\'avance.');
                        }

                    }catch (Exception $e){
                        $this->addFlash('error', 'La réservation ne peut être annulée. Cause : '.$e);
                    }

                    return $this->redirectToRoute('detailReservation',[
                        'succes' => false
                    ]);
                } else {
                    return $this->render('index/modalDetail.html.twig', [
                        'reservation' => $reservation,
                        'succes' => true,
                        'annulable' => $annulable,
                    ]);
                }
            }
        }else{
            return $this->render('index/modalDetail.html.twig', [
                'succes' => false
            ]);
        }
    }

    public function formReservation(SessionInterface $session, Request $request, ManagerRegistry $doctrine,Security $security)
    {
        $succes = true;
        $stop = true;
        // Récupération des variables dans l'url
        $id = $request->query->get('id', null);
        $heures = $request->query->get('heures', null);
        $date = $request->query->get('date', null);


        //récupe / création des objets en base
        $reservation = new Reservation();
        $user = $doctrine->getRepository(User::class)->find($security->getUser());
        $espace = $doctrine->getRepository(Espace::class)->find($id);

        // Test si un objet est null
        if ($espace == null){
            $this->addFlash('error', 'L\'espace que vous voulez réserver n\'est pas valide.');
            $succes = false;
        }
        if ($heures == null){
            $this->addFlash('error', 'L\'horaire n\'est pas valide.');
            $succes = false;
        }
        if ($date == null or $date < Date('d/m/Y')){
            $this->addFlash('error', 'La date de réservation n\'est pas valide.');
            $succes = false;
        }



        $date = explode("/",$date);
        $date = $date[2].'/'.$date[1].'/'.$date[0];

        // Définition des champs pour une réservation
        $reservation->setEspace($espace);
        $reservation->setDate(new DateTime($date));
        $reservation->setUser($user);

        if ($reservation->getEspace()->getTypeEspace()->getCategorie()->getId() != 3){
            // Réservation demi journée ou journée
            $form = $this->createForm(ReservationType::class, $reservation);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($form->get('matin')->isClicked()){
                    // Récupère s'il y a déjà une réservation sur un créneau horaire d'un jour et d'une salle
                    $reservationCount = $doctrine->getRepository(Reservation::class)->findExistingReservation(9, 13, $espace, $reservation->getDate());

                    // test si il y a déjà une réservation
                    if (count($reservationCount) === 0){
                        $reservation->setHeureDebut(9);
                        $reservation->setHeureFin(13);
                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                            if ($tarifs->getHeure() == 4){
                                $prix = $tarifs->getPrix();
                            }
                        }
                    }else{
                        $this->addFlash('error', 'La réservation ne peut pas être prise en compte. Il y a déjà une réservation sur ce créneau horaire.');
                        return $this->render('index/reservation.html.twig', [
                            'form' => $form,
                            'reservation' => $reservation,
                            'succes' => false,
                        ]);
                    }
                }elseif ($form->get('apresmidi')->isClicked()){
                    // Récupère s'il y a déjà une réservation sur un créneau horaire d'un jour et d'une salle
                    $reservationCount = $doctrine->getRepository(Reservation::class)->findExistingReservation(14, 18, $espace, $reservation->getDate());

                    // test si il y a déjà une réservation
                    if (count($reservationCount) === 0){
                        $reservation->setHeureDebut(14);
                        $reservation->setHeureFin(18);
                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                            if ($tarifs->getHeure() == 4){
                                $prix = $tarifs->getPrix();
                            }
                        }
                    }else{
                        $this->addFlash('error', 'La réservation ne peut pas être prise en compte. Il y a déjà une réservation sur ce créneau horaire.');
                        return $this->render('index/reservation.html.twig', [
                            'form' => $form,
                            'reservation' => $reservation,
                            'succes' => false,
                        ]);
                    }
                }elseif ($form->get('journee')->isClicked()){
                    // Récupère s'il y a déjà une réservation sur un créneau horaire d'un jour et d'une salle
                    $reservationCount = $doctrine->getRepository(Reservation::class)->findExistingReservation(9, 18, $espace, $reservation->getDate());

                    // test si il y a déjà une réservation
                    if (count($reservationCount) === 0){
                        $reservation->setHeureDebut(9);
                        $reservation->setHeureFin(18);
                        foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                            if ($tarifs->getHeure() == 9){
                                $prix = $tarifs->getPrix();
                            }
                        }
                    }else{
                        $this->addFlash('error', 'La réservation ne peut pas être prise en compte. Il y a déjà une réservation sur ce créneau horaire.');
                        return $this->render('index/reservation.html.twig', [
                            'form' => $form,
                            'reservation' => $reservation,
                            'succes' => false,
                        ]);
                    }
                }else{
                    $this->addFlash('error', 'Ce bouton n\'existe pas dans le formulaire');
                    return $this->render('index/reservation.html.twig', [
                        'form' => $form,
                        'reservation' => $reservation,
                        'succes' => false,
                    ]);
                }
                if ($user->getIndependant() != null){
                    $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
                    $destName = $inde->getNom().' '.$inde->getPrenom();
                    $user = $inde;
                }else if ($user->getEntreprise() != null){
                    $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                    $destName = $entr->getNom().' '.$entr->getPrenom();
                    $user = $entr;
                }else{
                    $destName = 'Admin';
                    $user = 'Admin';
                }
                try{
                    $reservation->setQuantite($prix);
                    $bourseApres = $reservation->getUser()->getBourse()->getQuantite()-$prix;

                    if ($user != 'Admin'){
                        if ($bourseApres < 0){
                            $this->addFlash('error', 'Vous n\'avez pas un solde suffisant pour cet espace');
                            return $this->render('index/reservation.html.twig', [
                                'succes' => false
                            ]);
                        }else{
                            $reservation->getUser()->getBourse()->setQuantite($bourseApres);
                        }
                    }


                }catch (Exception $e){
                    $stop = false;
                    $this->addFlash('error', 'Oops! Quelque chose s\'est mal passé et nous avons pas pu vous débiter.');
                    $this->addFlash('error', 'La Bananerie a été notifié de cette erreur.');
                }

                if ($stop){
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($reservation);
                    $entityManager->flush();

                    $this->addFlash('success', 'Merci ! La réservation a bien été pris en compte.');

                    // Envoi du mail pour confirmation
                    $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
                    $mailer = new Mailer($transport);
                    $email = (new Email())
                        ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
                        ->to($security->getUser()->getUserIdentifier())
                        ->subject("Nouvelle réservation : ". $espace->getLibelle() ) // Sujet
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
                                <h2>CONFIRMATION DE RÉSERVATION</h2>
                            </div>
                            <div style="padding: 20px;
                                text-align: left;">
                                <p>Bonjour '. $destName .',</p>
                                <p>Votre réservation à La Bananerie a bien été prise en compte.</p>
                                <p>Détails de réservation:</p>
                                <ul>
                                    <li>Espace : '. $reservation->getEspace()->getLibelle() .'</li>
                                    <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                                    <li>Heure : '. $reservation->getHeureDebut() .'h à '. $reservation->getHeureFin() .'h</li>
                                    <li>Detail de votre réservation : '.$reservation->getLibelle().'</li>
                                </ul>
                                <p>Toute annulation reste possible jusqu’à 48h avant la date effective de réservation.</p>
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
                        $this->addFlash('success', 'Merci ! Une confirmation vous a été envoyé par mail.');
                    } catch (TransportExceptionInterface $e) {
                        $this->addFlash('error', "Oops! Quelque chose s'est mal passé, nous n'avons pas pu envoyer la confirmation de réservation par mail.");
                    }
                }
                return $this->render('index/reservationHeure.html.twig', [
                    'form' => $form,
                    'reservation' => $reservation,
                    'succes' => false,
                ]);

            }else{
                return $this->render('index/reservation.html.twig', [
                    'form' => $form,
                    'reservation' => $reservation,
                    'succes' => $succes,
                ]);
            }

        }else{
            // Réservation à l'heure
            $form = $this->createForm(ReservationHeureType::class, $reservation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Récupère s'il y a déjà une réservation sur un créneau horaire d'un jour et d'une salle
                $reservationCount = $doctrine->getRepository(Reservation::class)->findExistingReservation($form['heureDebut']->getData(), $form['heureFin']->getData(), $espace, $reservation->getDate());

                // test si il y a déjà une réservation
                if (count($reservationCount) === 0){
                    $stop = true;
                }else{
                    $stop = false;
                }

                if ($stop){
                    if ($user->getIndependant() != null){
                        $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
                        $destName = $inde->getNom().' '.$inde->getPrenom();
                        $user = $inde;
                    }else if ($user->getEntreprise() != null){
                        $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                        $destName = $entr->getNom().' '.$entr->getPrenom();
                        $user = $entr;
                    }else{
                        $destName = 'Admin';
                        $user = 'Admin';
                    }

                    // Débit de B.Coins
                    try {
                        if ($user != 'Admin'){
                            $nbHeure = $reservation->getHeureFin() - $reservation->getHeureDebut();

                            if ($nbHeure < 4){
                                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                    if ($tarifs->getHeure() == 1){
                                        $prix = $tarifs->getPrix() * $nbHeure;
                                    }
                                }
                            }elseif ($nbHeure < 9){
                                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                    if ($tarifs->getHeure() == 4){
                                        $prix = $tarifs->getPrix();
                                    }
                                }
                                $nbHeure = $nbHeure - 4;
                                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                    if ($tarifs->getHeure() == 1){
                                        $prix = $tarifs->getPrix() * $nbHeure + $prix;
                                    }
                                }
                            }elseif ($nbHeure == 9){
                                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                                    if ($tarifs->getHeure() == 9){
                                        $prix = $tarifs->getPrix();
                                    }
                                }
                            }
                            $bourseApres = $reservation->getUser()->getBourse()->getQuantite()-$prix;

                            if ($bourseApres < 0){
                                $this->addFlash('error', 'Vous n\'avez pas un solde suffisant pour cet espace');
                                return $this->render('index/reservation.html.twig', [
                                    'succes' => false
                                ]);
                            }else{
                                $reservation->getUser()->getBourse()->setQuantite($bourseApres);
                            }

                        }

                    }catch (Exception $e){
                        $stop = false;
                        $this->addFlash('error', 'Oops! Quelque chose s\'est mal passé et nous avons pas pu vous débiter.');
                        $this->addFlash('error', 'La Bananerie a été notifié de cette erreur.');
                    }

                    if ($stop){
                        $entityManager = $doctrine->getManager();
                        $entityManager->persist($reservation);
                        $entityManager->flush();

                        $this->addFlash('success', 'Merci ! La réservation a bien été pris en compte.');

                        // Envoi du mail pour confirmation
                        $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
                        $mailer = new Mailer($transport);
                        $email = (new Email())
                            ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
                            ->to($security->getUser()->getUserIdentifier())
                            ->subject("Nouvelle réservation : ". $espace->getLibelle() ) // Sujet
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
                                <h2>CONFIRMATION DE RÉSERVATION</h2>
                            </div>
                            <div style="padding: 20px;
                                text-align: left;">
                                <p>Bonjour '. $destName .',</p>
                                <p>Votre réservation à La Bananerie a bien été prise en compte.</p>
                                <p>Détails de réservation:</p>
                                <ul>
                                    <li>Espace : '. $reservation->getEspace()->getLibelle() .'</li>
                                    <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                                    <li>Heure : '. $reservation->getHeureDebut() .'h à '. $reservation->getHeureFin() .'h</li>
                                    <li>Detail de votre réservation : '.$reservation->getLibelle().'</li>
                                </ul>
                                <p>Toute annulation reste possible jusqu’à 48h avant la date effective de réservation.</p>
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
                            $this->addFlash('success', 'Merci ! Une confirmation vous a été envoyé par mail.');
                        } catch (TransportExceptionInterface $e) {
                            $this->addFlash('error', "Oops! Quelque chose s'est mal passé, nous n'avons pas pu envoyer la confirmation de réservation par mail.");
                        }
                    }
                    return $this->render('index/reservationHeure.html.twig', [
                        'form' => $form,
                        'reservation' => $reservation,
                        'succes' => false,
                    ]);
                }else{
                    $this->addFlash('error', 'La réservation ne peut pas être prise en compte. Il y a déjà une réservation sur ce créneau horaire.');
                    return $this->render('index/reservationHeure.html.twig', [
                        'form' => $form,
                        'reservation' => $reservation,
                        'succes' => false,
                    ]);
                }
            }else{
                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                    if ($tarifs->getHeure() == 1){
                        $unite = $tarifs->getPrix();
                    }
                }
                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                    if ($tarifs->getHeure() == 4){
                        $demi = $tarifs->getPrix();
                    }
                }
                foreach ($reservation->getEspace()->getTarifEspaceTarifs() as $tarifs){
                    if ($tarifs->getHeure() == 9){
                        $journee = $tarifs->getPrix();
                    }
                }
                return $this->render('index/reservationHeure.html.twig', [
                    'form' => $form,
                    'reservation' => $reservation,
                    'succes' => $succes,
                    'unite' => $unite,
                    'demi' => $demi,
                    'journee' => $journee
                ]);
            }
        }
    }

    public function modalEspace(ManagerRegistry $doctrine, $idCarrousel, $idEspace)
    {
        $medias = $doctrine->getRepository(Media::class)->findBy(['carrousel' => $idCarrousel]);
        $espace = $doctrine->getRepository(Espace::class)->find($idEspace);

        return $this->render('index/modalEspace.html.twig', [
            'medias' => $medias,
            'espace' => $espace,
        ]);
    }

    public function choix(ManagerRegistry $doctrine): Response
    {
        return $this->render('index/choix.html.twig', [
            'controller_name' => 'IndexController',
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }
    public function mentionlegale(): Response
    {
        return $this->render('index/mentionlegale.html.twig');
    }
}
