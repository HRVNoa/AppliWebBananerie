<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\Tag;
use App\Entity\Statut;
use App\Entity\Metier;
use App\Entity\IndependantTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Collections\ArrayCollection;

class IndependantModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('entreprise', TextType::class)
            ->add('tel', TextType::class)
            ->add('dateNaiss', DateType::class)
            ->add('copos', TextType::class)
            ->add('email', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('statut', EntityType::class, [
                'class' => Statut::class,
                'choice_label' => 'libelle',
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'libelle',
            ])
            ->add('selectedTags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'attr' => ['class' => 'form-check', 'max-tags' => 10],
            ]);

        // Ajouter le champ pour sélectionner les super tags
        $builder->add('superTags', ChoiceType::class, [
            'choices' => $options['tags'],
            'choice_label' => function (Tag $tag) {
                return $tag->getLibelle();
            },
            'choice_value' => 'id',
            'expanded' => true,
            'multiple' => true,
            'mapped' => false,
        ]);

        $builder->add('enregistrer', SubmitType::class, ['label' => 'Modifier Independant', 'attr' => ['class' => 'btn btn-primary']]);


        // Gérer la soumission du formulaire
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $independant = $event->getData();
            $form = $event->getForm();
            $selectedTags = $form->get('selectedTags')->getData();
            $selectedSuperTags = $form->get('superTags')->getData();

            // Synchroniser les IndependantTag
            $currentTags = new ArrayCollection();
            foreach ($independant->getIndependantTags() as $independantTag) {
                $currentTags->add($independantTag->getTag());
            }

            foreach ($selectedTags as $tag) {
                if (!$currentTags->contains($tag)) {
                    $independantTag = new IndependantTag();
                    $independantTag->setIndependant($independant);
                    $independantTag->setTag($tag);
                    $independantTag->setSuper(false);
                    $independant->addIndependantTag($independantTag);
                }
            }

            foreach ($independant->getIndependantTags() as $independantTag) {
                if (!$selectedTags->contains($independantTag->getTag())) {
                    $independant->removeIndependantTag($independantTag);
                } else {
                    // Mettre à jour le statut super en fonction de la sélection de l'utilisateur
                    $independantTag->setSuper(in_array($independantTag->getTag(), $selectedSuperTags, false));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
            'tags' => [],
        ]);
    }
}
