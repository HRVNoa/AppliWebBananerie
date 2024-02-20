<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\Paiement;
use App\Entity\Tarif;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayementController extends AbstractController
{

    private UrlGeneratorInterface $generator;

    public function __construct(UrlGeneratorInterface $generator){
        $this->generator = $generator;
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
            $paiement->setBourse($bourse);
            $paiement->setUser($user);
            $paiement->setStatut($independant->getStatut());
            $paiement->setMetier($independant->getMetier());
            $paiement->setNom($independant->getNom());
            $paiement->setPrenom($independant->getPrenom());
            $paiement->setEntreprise(null);
            $paiement->setEmail($independant->getEmail());
            $paiement->setTel($independant->getTel());
            $paiement->setAdresse($independant->getAdresse());
            $paiement->setDateAchat($date);
        } elseif($user == $entreprise->getUser()){
            $paiement->setTarif($tarif);
            $paiement->setBourse($bourse);
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

        $entityManager = $doctrine->getManager();
        $entityManager->persist($paiement);
        $entityManager->flush();

        return $this->render('payement/success.html.twig', [
            'user' => $id,
        ]);
    }

    #[Route('/bourse/acheter/fail/{id}/{quantite}', name: 'payment_fail')]
    public function achatFail($id): Response{
        return $this->render('payement/fail.html.twig', [
            'user' => $id,
        ]);
    }


}
