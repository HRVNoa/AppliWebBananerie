<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Entity\Tarif;
use App\Entity\User;
use App\Form\PaiementAjouterType;
use App\Form\PaiementModifierType;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayementController extends AbstractController
{

    private UrlGeneratorInterface $generator;

    public function __construct(UrlGeneratorInterface $generator){
        $this->generator = $generator;
    }

    public function consulterPayement(ManagerRegistry $doctrine, $id){
        $paiement = $doctrine->getRepository(Paiement::class)->find($id);

        if(!$paiement){
            throw $this->createNotFoundException('Aucun paiement as été trouver avec le numéro' . $id);
        }

        return $this->render('payement/consulter.html.twig', [
            'paiement' => $paiement,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function listerPayement(ManagerRegistry $doctrine){
        $paiements = $doctrine->getRepository(Paiement::class)->findBy([], ['dateAchat' => 'DESC']);

        return $this->render('payement/lister.html.twig', [
            'paiements' => $paiements,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function listerComptePayement(ManagerRegistry $doctrine, Security $security, $id){
        //$remboursements = $doctrine->getRepository(Remboursement::class)->findBy(['user' => $id], ['dateRemboursement' => 'DESC']);
        $reservations = $doctrine->getRepository(Reservation::class) ->findBy(['user' => $id], ['date' => 'DESC']);
        $paiements = $doctrine->getRepository(Paiement::class)->findBy(['user' => $id], ['dateAchat' => 'DESC']);
        $user = $doctrine->getRepository(User::class)->find($id);
        $currentUser = $security->getUser();

        if ($currentUser !== $user) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à voir le relevé de compte d\'un autre utilisateur.');
            return $this->redirectToRoute('accueilIndex');
        }

        foreach($paiements as $paiement){
            $paiement->type = 'paiement';
        }
        foreach($reservations as $reservation){
            $reservation->type = 'reservation';
        }
        /*
        foreach($remboursements as $remboursement){
            $remboursement->type = 'remboursement';
        }
        */
        $quantite = $reservation->getQuantite();
        $items = array_merge($paiements, $reservations, /*$remboursements*/);


        return $this->render('payement/releverCompteLister.html.twig', [
            'items' => $items,
            'quantite' => $quantite,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function ajouterPayement(ManagerRegistry $doctrine, Request $request){
        $paiement = new Paiement();

        $form = $this->createForm(PaiementAjouterType::class, $paiement);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $paiement = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('payementLister', [
                'paiement' => $paiement,
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true)
            ]);
        } else {
            return $this->render('payement/ajouter.html.twig', array('form' => $form->createView()));
        }
    }

    public function modifierPayement(ManagerRegistry $doctrine, Request $request, $id){
        $paiement = $doctrine->getRepository(Paiement::class)->find($id);

        if(!$paiement){
            throw $this->createNotFoundException('Aucun paiement trouvé avec le numéro ' . $id);
        } else {
            $form = $this->createForm(PaiementModifierType::class, $paiement);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $paiement = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($paiement);
                $entityManager->flush();

                return $this->redirectToRoute('payementLister', [
                    'paiement' => $paiement,
                    'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true)
                ]);
            } else{
                return $this->render('payement/modifier.html.twig', array('form' => $form->createView()));
            }
        }
    }

    public function startPayement(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $tarifs = $doctrine->getRepository(Tarif::class)->findAll();
        $bourse = $doctrine->getRepository(Bourse::class)->findOneBy(['user' => $id]);

        $tarifSelect = $request->request->get('tarifSelect', 0);
        $tarif = $doctrine->getRepository(Tarif::class)->findOneBy(['quantite' => $tarifSelect]);

        $key = $_ENV['STRIPESECRETKEY'];
        Stripe::setApiKey($key);
        $productStripe = [];
        if ($tarif !== null) {
            foreach ($tarif as $product) {
                $productStripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => 500,
                        'product_data' => [
                            'name' => 'Banana Coins'
                        ]
                    ],
                    'quantity' => $tarifSelect,
                ];
            }

            $checkout_session = \Stripe\Checkout\Session::create([
                'customer_email' => $this->getUser()->getEmail(),
                'payment_method_types' => ['card'],
                'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'unit_amount' => 500,
                            'product_data' => [
                                'name' => 'Banana Coins'
                            ]
                        ],
                        'quantity' => $tarifSelect,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => $this->generator->generate('payment_success', [
                    'id' => $id,
                    'quantite' => $tarif->getQuantite(),
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generator->generate('payment_fail', [
                    'id' => $id,
                    'quantite' => $tarif->getQuantite(),
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'automatic_tax' => [
                    'enabled' => true,
                ],
            ]);

            /*
            return $this->redirectToRoute('accueilIndex', [
                'bourse' => $bourse,
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
            */
            return new RedirectResponse($checkout_session->url);
        } else {
            return $this->render('payement/payement.html.twig', [
                'bourse' => $bourse,
                'tarifs' => $tarifs,
                'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            ]);
        }
    }

    #[Route('/bourse/acheter/success/{id}/{quantite}', name: 'payment_success')]
    public function achatSuccess(ManagerRegistry $doctrine, Request $request, $quantite, $id): Response{
        $paiement = new Paiement();

        $tarifs = $doctrine->getRepository(Tarif::class)->findAll();
        $bourse = $doctrine->getRepository(Bourse::class)->findOneBy(['user' => $id]);
        $user = $doctrine->getRepository(User::class)->find($id);
        $independant = $doctrine->getRepository(Independant::class)->findOneBy(['user' => $id]);
        $entreprise = $doctrine->getRepository(Entreprise::class)->findOneBy(['user' => $id]);
        $date = new \DateTime();

        $tarif = $doctrine->getRepository(Tarif::class)->findOneBy(['quantite' => $quantite]);
        if($user == $independant->getUser()){
            $paiement->setTarif($tarif);
            $paiement->setUser($user);
            $paiement->setStatut($independant->getStatut());
            $paiement->setMetier($independant->getMetier());
            $paiement->setNom($independant->getNom());
            $paiement->setPrenom($independant->getPrenom());
            $paiement->setEntreprise($independant->getEntreprise());
            $paiement->setEmail($independant->getEmail());
            $paiement->setTel($independant->getTel());
            $paiement->setAdresse($independant->getAdresse());
            $paiement->setDateAchat($date);
        } elseif($user == $entreprise->getUser()){
            $paiement->setTarif($tarif);
            $paiement->setUser($user);
            $paiement->setStatut($entreprise->getStatut());
            $paiement->setMetier($entreprise->getMetier());
            $paiement->setNom($entreprise->getNom());
            $paiement->setPrenom($entreprise->getPrenom());
            $paiement->setEntreprise($entreprise->getNomStructure());
            $paiement->setEmail($entreprise->getEmail());
            $paiement->setTel($entreprise->getTel());
            $paiement->setAdresse($entreprise->getAdresse());
            $paiement->setDateAchat($date);
        }
        $bourse->setQuantite($bourse->getQuantite() + $quantite);
        try {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();
            $this->addFlash('success', 'Merci ! Votre commande a bien été crediter sur votre compte.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', "Oops! Quelque chose s'est mal passé.");
        }
        return $this->redirectToRoute('accueilIndex' , ['user' => $user]);
    }

    #[Route('/bourse/acheter/fail/{id}/{quantite}', name: 'payment_fail')]
    public function achatFail($id, ManagerRegistry $doctrine): Response{
        $user = $doctrine->getRepository(User::class)->find($id);
        $this->addFlash('error', "Oops! Quelque chose s'est mal passé.");
        return $this->redirectToRoute('accueilIndex' , ['user' => $user]);
    }
}
