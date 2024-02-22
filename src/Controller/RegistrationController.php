<?php

namespace App\Controller;

use App\Entity\Bourse;
use App\Entity\Entreprise;
use App\Entity\Independant;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ManagerRegistry $doctrine ,SessionInterface $session): Response
    {
        $user = new User();
        $email = $session->get('user_email');
        $form = $this->createForm(RegistrationFormType::class, $user, ['email'=> $email]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entrepriseId = $session->get('entreprise_id');
            $independantId = $session->get('independant_id');

            if ($entrepriseId) {
                $entreprise = $doctrine->getRepository(Entreprise::class)->find($entrepriseId);
                if ($entreprise) {
                    $user->setEntreprise($entreprise);
                    $entreprise->setUser($user);
                    $user->setRoles(["ROLE_ENTREPRISE"]);
                }
                $session->remove('entreprise_id');
            } elseif ($independantId) {
                $independant = $doctrine->getRepository(Independant::class)->find($independantId);
                if ($independant) {
                    $user->setIndependant($independant);
                    $independant->setUser($user);
                    $user->setRoles(["ROLE_INDEPENDANT"]);
                }
                $session->remove('independant_id');
            } else {
                $this->addFlash("error","“Vous devez choisir entre la pilule bleu ou la pilule rouge“");
                return $this->redirectToRoute("choixInscription");

            }
            $bourse = new Bourse();
            $bourse->setQuantite(0);
            $user->setBourse($bourse);
            $user->setConfirmed(0);
            $entityManager->persist($user);
            $entityManager->flush();
            $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from(new Address('bananeriebot@gmail.com', 'Contact La Bananerie'))
                ->to($user->getUserIdentifier())
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
            Bienvenue sur l\'application de la Bananerie!
        </div>
        <div style="padding: 20px;
            text-align: center;">
            <h1>Bonjour !</h1>
            <p>Votre demande d’inscription à l’espace membre de La Bananerie a bien été prise en compte. </p>
            <p>L’administrateur s’occupe de confirmer votre accès à la plateforme dans les plus brefs délais.</p>
            <p>Pour toute réservation ou demande urgente, n’hésitez pas à contacter l’équipe par mail à icilabananerie@gmail.com ou par téléphone au 06 80 81 74 79.</p>
            <p>A très bientôt à La Bananerie !</p>
            <p>
            LA BANANERIE<br>
            134 Bd Leroy<br>
            14 000 CAEN
            </p>
        </div>
    </div>
</body>
</html>
    ');
            try {
                $mailer->send($email);
                $this->addFlash('succesmail', 'Votre e-mail a été envoyé.');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('errormail', "Oops! Quelque chose s'est mal passé et nous n'avons pas pu envoyer votre message.");
            }


            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
