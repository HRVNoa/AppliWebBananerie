<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Espace;
use App\Entity\Independant;
use App\Entity\Reservation;
use App\Entity\TypeEspace;
use App\Entity\User;
use App\Form\ReservationType;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
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
use function PHPUnit\Framework\returnArgument;

class IndexController extends AbstractController
{
    public function index(Security $security, ManagerRegistry $doctrine, Request $request): Response
    {
        if ($security->getUser()){

            $userReservations = $doctrine->getRepository(Reservation::class)->getReservationFutureByUser($security->getUser());

            $jour = $request->query->get('jour', date("d/m/Y"));
            if ($jour <= date("d/m/Y")){
                $jour = date("d/m/Y");
            }
            $jourFind = explode("/",$jour);
            $jourFind = $jourFind[2].'/'.$jourFind[1].'/'.$jourFind[0];
            $espaces = $doctrine->getRepository(Espace::class)->findAll();
            $reservations = $doctrine->getRepository(Reservation::class)->findBy(['date' => new DateTime($jourFind)]);

            return $this->render('index/index.html.twig', [
                'espaces' => $espaces,
                'reservations' => $reservations,
                'userReservations' => $userReservations,
                'jour' => $jour,
            ]);
        }
        return $this->redirectToRoute('app_login');
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
                $form = $this->createForm(ReservationType::class, $reservation);
                $form->handleRequest($request);
                $now = new DateTime('now');
                if ($reservation->getDate()->modify("-2 day") < $now){
                    $annulable = false;
                }else{
                    $annulable = true;
                }

                if ($form->isSubmitted() && $form->isValid()) {
                    try {
                        if ($annulable){
                            //Annulation de la reservation

                            $connectedUser = $security->getUser();
                            if ($reservation->getUser()->getIndependant() != null){
                                $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
                                $destName = $inde->getNom().' '.$inde->getPrenom();
                            }else if ($reservation->getUser()->getEntreprise() != null){
                                $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                                $destName = $entr->getNom().' '.$entr->getPrenom();
                            }else{
                                $destName = 'Admin';
                            }

                            $entityManager = $doctrine->getManager();
                            $entityManager->remove($reservation);
                            $entityManager->flush();

                            $this->addFlash('success', 'La réservation à bien été annulée');

                            // Envoi du mail pour confirmation
                            $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
                            $mailer = new Mailer($transport);
                            $email = (new Email())
                                ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
                                ->to($security->getUser()->getUserIdentifier())
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
                                        <p>Nous sommes désolés de vous informer que votre réservation a été annulée. Voici les détails de la réservation annulée :</p>
                                        <ul>
                                            <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                                            <li>Heure : '. $reservation->getHeureDebut()->format("H") .'h à '. $reservation->getHeureFin()->format("H") .'h</li>
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
                                $this->addFlash('success', 'Un accusé vous a été envoyé par mail.');
                            } catch (TransportExceptionInterface $e) {
                                $this->addFlash('error', "Oops! Quelque chose s'est mal passé et nous n'avons pas pu envoyer un accusé de la réservation par email.");
                            }

                            return $this->render('index/reservation.html.twig', [
                                'form' => $form,
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
                        'form' => $form,
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

    public function formReservation(Request $request, ManagerRegistry $doctrine)
    {
        $succes = true;
        $id = $request->query->get('id', null);
        $heures = $request->query->get('heures');
        $date = $request->query->get('date', null);
        $espace = $doctrine->getRepository(Espace::class)->find($id);
        if ($espace == null){
            $this->addFlash('error', 'L\'espace que vous voulez réserver n\'est pas valide.');
            $succes = false;
        }
        if ($date == null or $date < Date('d/m/Y')){
            $this->addFlash('error', 'La date de réservation n\'est pas valide.');
            $succes = false;
        }
        if ($heures == 'j' or $heures == 'm' or $heures == 'a'){
            $succes = true;
        }else{
            $this->addFlash('error', 'Les horraires de réservation de ne sont pas valide.');
            $succes = false;
        }


        return $this->redirectToRoute('showFormReservation', [
            'id' => $id,
            'heures' => $heures,
            'date' => $date,
            'succes' => $succes
        ]);
    }

    public function showFormReservation(SessionInterface $session, Request $request, ManagerRegistry $doctrine,Security $security)
    {
        $succes = $request->query->get('succes');

        $reservation = new Reservation();
        $id = $request->query->get('id', null);
        $heures = $request->query->get('heures');
        $date = $request->query->get('date', null);

        $date = explode("/",$date);
        $date = $date[2].'/'.$date[1].'/'.$date[0];

        $stop = false;
        $espace = $doctrine->getRepository(Espace::class)->find($id);
        $reservation->setEspace($espace);
        $reservation->setDate(new DateTime($date));
        $reservation->setUser($security->getUser());

        $reservationCount = 0;
        if ($heures === 'm'){
            $espaceMatin = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'09:00:00', '13:00:00',$id);
            $espaceJournee = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'09:00:00', '18:00:00',$id);
            if ($espaceMatin or $espaceJournee){
                $reservationCount += 1;
            }
        }elseif ($heures === 'a'){
            $espaceApres = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'14:00:00', '18:00:00',$id);
            $espaceJournee = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'09:00:00', '18:00:00',$id);
            if ($espaceApres or $espaceJournee){
                $reservationCount += 2;
            }
        }elseif ($heures === 'j'){
            $espaceMatin = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'09:00:00', '13:00:00',$id);
            $espaceApres = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'14:00:00', '18:00:00',$id);
            $espaceJournee = $doctrine->getRepository(Reservation::class)->getReservationByEspace($date,'09:00:00', '18:00:00',$id);
            if ($espaceJournee or $espaceMatin or $espaceApres){
                $reservationCount += 4;
            }
        }

        if ($heures == 'm' and $reservationCount != 1){
            $reservation->setHeureDebut(new DateTime('09:00:00'));
            $reservation->setHeureFin(new DateTime('13:00:00'));
        }elseif ($heures == 'a'and $reservationCount != 2){
            $reservation->setHeureDebut(new DateTime('14:00:00'));
            $reservation->setHeureFin(new DateTime('18:00:00'));
        }elseif ($heures == 'j' and $reservationCount != 4){
            $reservation->setHeureDebut(new DateTime('09:00:00'));
            $reservation->setHeureFin(new DateTime('18:00:00'));
        }else{
            $stop = true;
        }

        if (!$stop) {
            $form = $this->createForm(ReservationType::class, $reservation);
            $form->handleRequest($request);

            $reservation->setEspace($session->get('salleId', $reservation->getEspace()));
            if ($form->isSubmitted() && $form->isValid()) {

                $connectedUser = $security->getUser();
                if ($reservation->getUser()->getIndependant() != null){
                    $inde = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $reservation->getUser()]);
                    $destName = $inde->getNom().' '.$inde->getPrenom();
                }else if ($reservation->getUser()->getEntreprise() != null){
                    $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                    $destName = $entr->getNom().' '.$entr->getPrenom();
                }else{
                    $destName = 'Admin';
                }

                $data = $form->getData();

                $entityManager = $doctrine->getManager();
                $entityManager->persist($data);
                $entityManager->flush();


                $this->addFlash('success', 'La réservation à bien été pris en compte.');

                // Envoi du mail pour confirmation
                $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
                $mailer = new Mailer($transport);
                $email = (new Email())
                    ->from(new Address('bananeriebot@gmail.com', 'La Bananerie'))
                    ->to($security->getUser()->getUserIdentifier())
                    ->subject("Nouvelle réservation : ". $espace->getLibelle() ) // Sujet
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
                                <h2>Confirmation de votre réservation</h2>
                            </div>
                            <div style="padding: 20px;
                                text-align: left;">
                                <p>Bonjour '. $destName .',</p>
                                <p>Nous sommes ravis de confirmer votre réservation. Voici les détails :</p>
                                <ul>
                                    <li>Date : '. $reservation->getDate()->format("d/m/Y") .'</li>
                                    <li>Heure : '. $reservation->getHeureDebut()->format("H") .'h à '. $reservation->getHeureFin()->format("H") .'h</li>
                                    <li>Detail de votre réservation : '.$reservation->getLibelle().'</li>
                                </ul>
                                <p>Pour toute modification ou annulation, veuillez nous contacter la bananerie ou via l\'espace membre.</p>
                                <p>Nous avons hâte de vous accueillir !</p>
                                <p>Cordialement,</p>
                                <p>La Bananerie.</p>
                            </div>
                            <div style="background-color: #333333;
                                color: #ffffff;
                                padding: 10px;
                                text-align: center;
                                font-size: 12px;">
                                Merci de choisir La Bananerie | <a href="https://google.fr" style="color: #ffffff;">Visitez notre site</a>
                            </div>
                        </div>
                    ');
                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'Merci! Un accusé vous a été envoyé par mail.');
                } catch (TransportExceptionInterface $e) {
                    $this->addFlash('error', "Oops! Quelque chose s'est mal passé et nous n'avons pas pu envoyer un accusé de la réservation par email.");
                }

                return $this->render('index/reservation.html.twig', [
                    'form' => $form,
                    'reservation' => $reservation,
                    'succes' => false
                ]);

            }else{
                return $this->render('index/reservation.html.twig', [
                    'form' => $form,
                    'reservation' => $reservation,
                    'succes' => $succes
                ]);
            }
        }else{
            $this->addFlash('error', 'La réservation ne peut pas être prise.');
            return $this->render('index/reservation.html.twig', [
                'succes' => false
            ]);
        }
    }

    public function choix(): Response
    {
        return $this->render('index/choix.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
