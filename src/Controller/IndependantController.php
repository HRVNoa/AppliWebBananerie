<?php

namespace App\Controller;

use App\Entity\Carrousel;
use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Media;
use App\Entity\Tag;
use App\Entity\Video;
use App\Form\IndependantContactType;
use App\Form\IndependantModifierPhotoDeProfilType;
use App\Form\MediaType;
use App\Form\IndependantModifierTagType;
use App\Form\IndependantModifierType;
use App\Form\IndependantType;
use App\Form\VideoType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
class IndependantController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('independant/index.html.twig', [
            'controller_name' => 'IndependantController',
        ]);
    }
    public function consulterIndependant(ManagerRegistry $doctrine, $id){

        $independant= $doctrine->getRepository(Independant::class)->find($id);
        $carrousel = $independant->getPortfolio();
        $media = $carrousel->getMedia();


        $form = $this->createForm(IndependantContactType::class);
        if (!$independant) {
            throw $this->createNotFoundException(
                'Aucun independant trouvé avec le numéro '.$id
            );
        }


        return $this->render('independant/consulter.html.twig', [
            'independant' => $independant,
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            'form' => $form,
            'carrousel' => $carrousel,
            'media' => $media]);
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
        $carrousel = new Carrousel();
        $independant->setPortfolio($carrousel);
        $carrousel->setIndependant($independant);
        $form = $this->createForm(IndependantType::class, $independant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $independant = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($independant);
            $entityManager->persist($carrousel);
            $entityManager->flush();

            // Stockez l'ID de l'independant dans la session
            $session->set('independant_id', $independant->getId());
            $session->set('user_email', $independant->getEmail());
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
        $independant->setUser(null);
        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        }

        $entityManager->remove($independant);
        $entityManager->flush();
        return $this->redirectToRoute('independantLister', [
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
        ]);
    }

    public function sendmailIndependant(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $independant = $entityManager->getRepository(Independant::class)->find($id);
        $form = $this->createForm(IndependantContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $transport = Transport::fromDsn('smtp://bananeriebot@gmail.com:ramchihfwonbusnl@smtp.gmail.com:587?encryption=tls&auth_mode=login');
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from(new Address('bananeriebot@gmail.com', 'Contact La Bananerie'))
                ->to($independant->getEmail())
                ->subject("[Contact Espace Membre]" . $formData['nom']) // Sujet
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

        return $this->render('independant/consulter.html.twig', [
            'independant' => $independant,
            'form' => $form->createView(),
        ]);
    }

    public function ModifInfoIndependant(ManagerRegistry $doctrine, $id, Request $request, Security $security)
    {

        $independant = $doctrine->getRepository(Independant::class)->find($id);
        $idinde = $independant->getId();
        $user = $independant->getUser();
        $carrousel = $independant->getPortfolio();
        $media = $carrousel->getMedia();

        $currentUser= $security->getUser();

        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        $conditionsRemplies = $independant->getMetier() !== null &&
            $independant->getIndependantTags()->count()  > 0 &&
            $independant->getPhotodeprofil() !== null;
        if (!$conditionsRemplies) {
            $this->addFlash('errorInfo', 'Vous devez avoir au moins 1 super tag et une photo de profil avant de pouvoir remplir vos informations complèmentaire.');
            return $this->redirectToRoute('independantConsulter',['id' => $idinde]); // Redirigez vers une page appropriée

        }
        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        }

        $form = $this->createForm(IndependantModifierType::class, $independant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $independant = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($independant);
            $entityManager->flush();
            return $this->redirectToRoute('independantConsulter', [
                'id' => $idinde,
            ]);
        } else {
            return $this->render('independant/formModifInfo.html.twig', ['form' => $form->createView(), 'independant' => $independant,]
            );
        }
    }

    public function ModifPhotoDeProfilIndependant(ManagerRegistry $doctrine, $id, Request $request, Security $security)
    {

        $independant = $doctrine->getRepository(Independant::class)->find($id); //récupération du média avec son id
        $oldFilename = $independant->getPhotodeprofil(); //récupération de son ancien nom de l'image
        $carrousel = $independant->getPortfolio();
        $media = $carrousel->getMedia();
        $idinde = $independant->getId();
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        if (!$independant) { // test si il y a un independant lier avec l'id
            $this->addFlash('error', 'L independant que vous voulais modifier n\'existe pas.');
            return $this->redirectToRoute('independantConsulter');
        } else {
            //Création du formulaire photo de profil
            $form = $this->createForm(IndependantModifierPhotoDeProfilType::class, $independant);
            $form->handleRequest($request);

            //Si le formulaire est envoyer et si il est valide
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form['photodeprofil']->getData();
                if ($file instanceof UploadedFile) {

                    // supprime l'ancienne image
                    $oldFilePath = $this->getParameter('photoprofil') . '/' . $oldFilename;
                    if ($oldFilename && file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }

                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // récupère le nom de l'image uploaded
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); // Raison de sécurité mettre les caratères en lower et enlever les autres caratères qui n'est pas des lettres et des chiffres
                    $newFilename = $safeFilename . '.' . $file->guessExtension(); // Nommage de l'image dans le dossier avec l'extension qui convient

                    // Dépose l'image dans le répertoire média
                    try {
                        $file->move($this->getParameter('photoprofil'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        echo $e;
                    }

                    // Défini l'image dans le champs lien de l'entité média
                    $independant->setPhotodeprofil($newFilename);
                }

                $formData = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($formData);
                $entityManager->flush();
                $this->addFlash('success', 'La photo de profil a été mis à jour avec succès.');
                return $this->redirectToRoute('independantConsulter', [
                    'id' => $idinde,
                ]);
            } else {
                return $this->render('independant/formPhotodeProfil.html.twig', ['independant' => $independant, 'form' => $form->createView()]);
            }
        }
    }
    public function ModifTagIndependant(ManagerRegistry $doctrine, $id, Request $request, Security $security)
    {
        $independant = $doctrine->getRepository(Independant::class)->find($id);
        $idinde = $independant->getId();
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        }
        $tags = $doctrine->getRepository(Tag::class)->findAll();
        $selectedTags = $independant->getIndependantTags()->map(function($independantTag) {
            return $independantTag->getTag();
        })->toArray();

        $form = $this->createForm(IndependantModifierTagType::class, $independant, [
            'independant' => $independant,
            'tags' => $tags,
            'selected_tags' => $selectedTags,
        ]);
        $form->handleRequest($request);
        $entityManager = $doctrine->getManager();
        $independantTags = $doctrine->getRepository(IndependantTag::class)->findBy(['independant' => null]);

        foreach ($independantTags as $independantTag) {
            $entityManager->remove($independantTag);
        }
        $entityManager->flush();
        if ($form->isSubmitted() && $form->isValid()) {

            $selectedTags = $form->get('selectedTags')->getData();
            $superTags = $form->get('superTags')->getData();

            $selectedTagIds = array_map(function($tag) { return $tag->getId(); }, $selectedTags);
            $superTagIds = array_map(function($tag) { return $tag->getId(); }, $superTags);
            $regularTagsCount = count(array_diff($selectedTagIds, $superTagIds));

            if ($regularTagsCount > 10) {
                $this->addFlash("errortag","Vous devez choisir dix tags maximum");
                return $this->redirectToRoute("independantModifierTag", ['id' => $idinde]);
            }

            if (count($superTags) > 3) {
                $this->addFlash("errorsupertag","Vous devez choisir trois super tags");
                return $this->redirectToRoute("independantModifierTag", ['id' => $idinde]);
            }
            $entityManager = $doctrine->getManager();
            $independantTags = $doctrine->getRepository(IndependantTag::class)->findBy(['independant' => null]);

            foreach ($independantTags as $independantTag) {
                $entityManager->remove($independantTag);
            }
            $entityManager->flush();
            $independant = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($independant);
            $entityManager->flush();
            $this->addFlash('success', 'Vos tags ont été mis à jour avec succès.');
            return $this->redirectToRoute('independantConsulter', [
                'id' => $idinde,
            ]);
        } else {
            return $this->render('independant/formModifTag.html.twig', ['independant' => $independant, 'form' => $form->createView(), 'id ' => $idinde ,]);
        }
    }
    public function AddPortfolioIndependant(ManagerRegistry $doctrine, Request $request, $id , Security $security): Response
    {
        $independant = $doctrine->getRepository(Independant::class)->find($id);
        $carrouselid = $independant->getPortfolio()->getId();
        $media = $doctrine->getRepository(Media::class)->find($carrouselid);
        $medias = $doctrine->getRepository(Media::class)->findBy(['carrousel' => $carrouselid]);
        $idinde = $independant->getId();
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        if (!$independant) {
            $this->addFlash('error', 'Independant non trouvé.');
            return $this->redirectToRoute("independantConsulter", ['id' => $idinde]);
        }
        $carrousel = $independant->getPortfolio();
        if (!$carrousel) {
            $carrousel = new Carrousel();
            $doctrine->getManager()->persist($carrousel);
            $doctrine->getManager()->flush();
        }
        $media = new Media();
        $media->setCarrousel($carrousel);

        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($medias) >= 5) {
                $this->addFlash('errorphotos', 'Vous ne pouvez pas avoir plus de 5 photos.');
                return $this->redirectToRoute("independantCréerPortfolio", ['id' => $idinde]);
            }

            $file = $form['lien']->getData();
            if ($file instanceof UploadedFile) {
                $newFilename = $this->uploadFile($file, $media);
                $media->setLien($newFilename);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($media);
            $entityManager->flush();

            $this->addFlash('success', 'Le média a été ajouté avec succès.');
            return $this->redirectToRoute('independantCréerPortfolio', ['id' => $idinde]); // Remplacer 'some_route' par la route appropriée
        }

        return $this->render('independant/ajouterPortfolio.html.twig', [
            'form' => $form->createView(),
            'medias'=>$medias,
            'independant' => $independant,
        ]);
    }

    public function ModifPortfolioIndependant(ManagerRegistry $doctrine, Request $request, $id , Security $security): Response
    {
        $media = $doctrine->getRepository(Media::class)->find($id);

        $carrouselid = $doctrine->getRepository(Media::class)->find($media);

        $idinde = $carrouselid->getCarrousel()->getIndependant()->getId();

        $independant = $doctrine->getRepository(Independant::class)->find($idinde);
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        if (!$media) {
            $this->addFlash('error', 'Le média que vous voulez modifier n\'existe pas.');
            return $this->redirectToRoute('independantConsulter', ['independant' => $independant, 'id' => $idinde]); // Remplacer 'some_route' par la route appropriée
        }

        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['lien']->getData();
            if ($file instanceof UploadedFile) {
                // Suppression de l'ancienne image si elle existe
                $oldFilename = $media->getLien();
                if ($oldFilename) {
                    $this->removeFile($oldFilename);
                }

                // Traitement du nouveau fichier téléchargé
                $newFilename = $this->uploadFile($file, $media);
                $media->setLien($newFilename);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($media);
            $entityManager->flush();

            $this->addFlash('success', 'Le média a été mis à jour avec succès.');
            return $this->redirectToRoute('independantCréerPortfolio', ['independant'=> $independant,'id' => $idinde]);
        }

        return $this->render('independant/formModifPortfolio.html.twig', [
            'form' => $form->createView(),
            'quantiteBourse' => json_decode($this->forward('App\Controller\BourseController::getBourse', [$doctrine])->getContent(),true),
            'independant' => $independant,
        ]);
    }

    public function SupprimerPortfolioIndependant(ManagerRegistry $doctrine, Request $request , $id , Security $security): Response
    {
        $media = $doctrine->getRepository(Media::class)->find($id);

        $carrouselid = $doctrine->getRepository(Media::class)->find($media);

        $idinde = $carrouselid->getCarrousel()->getIndependant()->getId();

        $independant = $doctrine->getRepository(Independant::class)->find($idinde);


        if (!$media) {
            $this->addFlash('error', 'Le média que vous voulez supprimer n\'existe pas.');
            return $this->redirectToRoute('independantCréerPortfolio', ['independant' => $independant, 'id' => $idinde]);
        }

        $filename = $media->getLien();
        if ($filename) {
            $this->removeFile($filename);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($media);
        $entityManager->flush();

        $this->addFlash('success', 'Le média a été supprimé avec succès.');
        return $this->redirectToRoute('independantCréerPortfolio', ['independant' => $independant, 'id' => $idinde]);
    }

    private function uploadFile(UploadedFile $file, Media $media): string
    {
        // Récupération de l'indépendant lié au média
        $carrousel = $media->getCarrousel();
        $independant = $carrousel->getIndependant(); // Assurez-vous que cette méthode existe et fonctionne correctement

        // Construction du nom de fichier basé sur l'ID de l'indépendant et son nom
        $independantId = $independant->getId();
        $independantName = $independant->getNom(); // Remplacez 'getName' par la méthode réelle pour récupérer le nom de l'indépendant
        $safeIndependantName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $independantName);

        // Construction du nouveau nom de fichier
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $independantId . '_' . $safeIndependantName . '_' . transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename.'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter('portfolio'), // Assurez-vous de définir ce paramètre dans services.yaml
                $newFilename
            );
        } catch (FileException $e) {
            // Gérer l'exception si quelque chose se passe mal pendant le téléchargement du fichier
            throw $e;
        }

        return $newFilename;
    }


    private function removeFile(string $filename): void
    {
        $filePath = $this->getParameter('portfolio').'/'.$filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function modifierVideoIndependant(ManagerRegistry $doctrine, $id, Request $request , Security $security){

        $video = $doctrine->getRepository(Video::class)->find($id);
        $independant = $video->getIndependant();
        $idinde = $independant->getId();
        $videos = $independant->getVideos();
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        if (!$video) {
            throw $this->createNotFoundException('Aucune video trouvé avec le numéro '.$id);
        }
        else
        {

            $hasSuperVideo = false;
            foreach ($videos as $existingVideo) {
                if ($existingVideo->getId() != $video->getId() && $existingVideo->isSuper()) {
                    $hasSuperVideo = true;
                    break;
                }
            }
            $form = $this->createForm(VideoType::class, $video);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($hasSuperVideo && $video->isSuper()) {
                    $this->addFlash('errorsuper', 'Vous avez déjà une vidéo en super.');
                    return $this->redirectToRoute('independantListerVideo', [
                        'independant' => $independant,
                        'videos' => $videos,
                        'id' => $idinde,]);
                }
                $video = $form->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($video);
                $entityManager->flush();
                return $this->redirectToRoute('independantListerVideo', [
                    'independant' => $independant,
                    'videos' => $videos,
                    'id' => $idinde,]);
            }
            else{
                return $this->render('independant/ajouterVideo.html.twig', array('form' => $form->createView(),));
            }
        }
    }
    public function listerVideoIndependant(ManagerRegistry $doctrine, $id, Request $request , Security $security)
    {
        $independant = $doctrine->getRepository(Independant::class)->find($id);
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        if (!$independant) {
            throw $this->createNotFoundException('Aucun independant trouvé avec le numéro '.$id);
        } else {
            $idinde = $independant->getId();
            $videos = $doctrine->getRepository(Video::class)->findBy(['independant' => $idinde]);

            return $this->render( 'independant/listerVideo.html.twig', [
                'id' => $idinde,
                'independant' => $independant,
                'videos' => $videos,
            ]);
            }
    }
    public function SupprimerVideoIndependant(ManagerRegistry $doctrine, int $id , Security $security): Response
    {
        $video = $doctrine->getRepository(Video::class)->find($id);

        $independant = $doctrine->getRepository(Independant::class)->find($id);
        $idinde = $video->getIndependant()->getId();
        $videos = $video->getIndependant()->getVideos();
        $entityManager = $doctrine->getManager();

        $video = $entityManager->getRepository(Video::class)->find($id);
        $user = $independant->getUser();
        $currentUser= $security->getUser();
        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }

        if (!$video) {
            throw $this->createNotFoundException('Aucune vidéo trouvé avec le numéro '.$id);
        }
        $entityManager->remove($video);
        $entityManager->flush();
        return $this->redirectToRoute('independantListerVideo', [
            'independant' => $independant,
            'videos' => $videos,
            'id' => $idinde,]);
    }
    public function AjouterVideoIndependant(ManagerRegistry $doctrine,Request $request, $id , Security $security)
    {
        $independant = $doctrine->getRepository(Independant::class)->find($id);
        $video = new Video();
        $videos = $independant->getVideos();
        $idinde = $independant->getId();
        $user = $independant->getUser();
        $currentUser= $security->getUser();

        if ($currentUser !== $user && !$this->isGranted('ROLE_ADMIN')) {
            // Si les ID ne correspondent pas, redirigez l'utilisateur vers sa propre page
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations d\'un autre indépendant.');
            return $this->redirectToRoute('accueilIndex');
        }
        $hasSuperVideo = false;
        foreach ($videos as $existingVideo) {
            if ($existingVideo->isSuper()) {
                $hasSuperVideo = true;
                break;
            }
        }

        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($videos) >= 6) {
                $this->addFlash('errorlien', 'Vous ne pouvez pas avoir plus de 6 vidéos.');
                return $this->redirectToRoute("independantListerVideo", [
                    'independant' => $independant,
                    'videos' => $videos,
                    'id' => $idinde,]);
                }
            if ($hasSuperVideo && $video->isSuper()) {
                $this->addFlash('errorsuper', 'Vous avez déjà une vidéo mise en avant.');
                return $this->redirectToRoute("independantListerVideo", [
                    'independant' => $independant,
                    'videos' => $videos,
                    'id' => $idinde,]);
            }
            $video->setIndependant($independant);

            $video = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($video);
            $entityManager->flush();

            return $this->redirectToRoute('independantListerVideo',[

                'independant' => $independant,
                'videos' => $videos,
                'id' => $idinde,
            ]);
        } else {
            return $this->render('independant/ajouterVideo.html.twig', array('form' => $form->createView(),));
        }
    }
}
