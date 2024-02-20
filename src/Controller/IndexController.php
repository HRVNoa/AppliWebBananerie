<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Entreprise;
use App\Entity\Espace;
use App\Entity\Independant;
use App\Entity\Media;
use App\Entity\Reservation;
use App\Entity\TarifEspaceTarif;
use App\Entity\TypeEspace;
use App\Entity\User;
use App\Form\ReservationType;
use App\Form\TarifEspaceType;
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
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\returnArgument;

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
                            }else if ($reservation->getUser()->getEntreprise() != null){
                                $entr = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $reservation->getUser()]);
                                $destName = $entr->getNom().' '.$entr->getPrenom();
                            }else{
                                $destName = 'Admin';
                            }

                            $entityManager = $doctrine->getManager();
                            $entityManager->remove($reservation);
                            $entityManager->flush();

                            $this->addFlash('success', 'Merci ! La réservation a bien été annulée');

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

        $espace = $doctrine->getRepository(Espace::class)->find($id);
        $reservation->setEspace($espace);
        $reservation->setDate(new DateTime($date));
        $reservation->setUser($security->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        $reservation->setEspace($session->get('salleId', $reservation->getEspace()));
        if ($form->isSubmitted() && $form->isValid()) {
            $reservationCount = $doctrine->getRepository(Reservation::class)->findExistingReservation($form['heureDebut']->getData(), $form['heureFin']->getData(), $espace, $reservation->getDate());

            // test si il y a déjà une réservation
            if (count($reservationCount) === 0){
                $stop = true;
            }else{
                $stop = false;
            }
            if ($stop) {
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

            return $this->render('index/reservation.html.twig', [
                'form' => $form,
                'reservation' => $reservation,
                'succes' => false
            ]);
            }else{
                $this->addFlash('error', 'La réservation ne peut pas être prise en compte. Il y a déjà une réservation sur ce créneau horaire.');
                return $this->render('index/reservation.html.twig', [
                    'succes' => false
                ]);
            }
        }else{
            return $this->render('index/reservation.html.twig', [
                'form' => $form,
                'reservation' => $reservation,
                'succes' => $succes
            ]);
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
