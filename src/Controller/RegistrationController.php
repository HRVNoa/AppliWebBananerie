<?php

namespace App\Controller;

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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ManagerRegistry $doctrine ,SessionInterface $session): Response
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
            $entrepriseId = $session->get('entreprise_id');
            $independantId = $session->get('independant_id');

            if ($entrepriseId) {
                $entreprise = $doctrine->getRepository(Entreprise::class)->find($entrepriseId);
                if ($entreprise) {
                    $user->setEntreprise($entreprise);
                    $entreprise->setUser($user);
                }
                $session->remove('entreprise_id');
            } elseif ($independantId) {
                $independant = $doctrine->getRepository(Independant::class)->find($independantId);
                if ($independant) {
                    $user->setIndependant($independant);
                    $independant->setUser($user);
                }
                $session->remove('independant_id');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
