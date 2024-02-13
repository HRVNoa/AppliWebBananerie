<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependantModifierTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $independant = $options['independant'];
        $selectedTagsInitialValue = [];
        $superTagsInitialValue = [];

        if ($independant) {
            foreach ($independant->getIndependantTags() as $independantTag) {
                if (!$independantTag->isSuper()) { // Vérifiez si le tag n'est pas un super tag
                    $selectedTagsInitialValue[] = $independantTag->getTag();
                }

                // Si vous voulez toujours inclure les super tags dans les superTagsInitialValue, conservez cette partie
                if ($independantTag->isSuper()) {
                    $superTagsInitialValue[] = $independantTag->getTag();
                }
            }
        }

        $builder
            ->add('selectedTags', EntityType::class, [
                'class' => Tag::class,
                'choices' => $options['tags'], // Assuming you pass all Tag entities here
                'choice_label' => function (Tag $tag) {
                    return $tag->getLibelle();
                },
                'data' => $selectedTagsInitialValue, // Set initial values
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('superTags', EntityType::class, [
                'class' => Tag::class,
                'choices' => $options['tags'],
                'choice_label' => function (Tag $tag) {
                    return $tag->getLibelle(); // Assuming Tag entity has getLibelle() method
                },
                'data' => $superTagsInitialValue,
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('enregistrer', SubmitType::class, ['label' => 'Modifier', 'attr' => ['class' => 'btn btn-primary']]);



        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $independant = $event->getData();
            $form = $event->getForm();
            $selectedTags = $form->get('selectedTags')->getData();
            $selectedSuperTags = $form->get('superTags')->getData();

            // Créer une liste de tous les tags actuels
            $currentTags = new ArrayCollection();
            foreach ($independant->getIndependantTags() as $independantTag) {
                $currentTags->add($independantTag->getTag());
            }

            // Mettre à jour ou ajouter les selectedTags
            foreach ($selectedTags as $tag) {
                $isSuperTag = in_array($tag, $selectedSuperTags);
                if (!$currentTags->contains($tag)) {
                    // Si le tag n'est pas dans les tags actuels, l'ajouter
                    $independantTag = new IndependantTag();
                    $independantTag->setIndependant($independant);
                    $independantTag->setTag($tag);
                    $independantTag->setSuper($isSuperTag);
                    $independant->addIndependantTag($independantTag);
                } else {
                    // Si le tag est déjà présent, mettre à jour son statut super
                    foreach ($independant->getIndependantTags() as $independantTag) {
                        if ($independantTag->getTag() === $tag) {
                            $independantTag->setSuper($isSuperTag);
                            break;
                        }
                    }
                }
            }

            // Assurez-vous que tous les superTags sont traités, même s'ils ne sont pas dans selectedTags
            foreach ($selectedSuperTags as $superTag) {
                if (!$currentTags->contains($superTag)) {
                    // Ajoutez le superTag s'il n'est pas déjà inclus
                    $independantTag = new IndependantTag();
                    $independantTag->setIndependant($independant);
                    $independantTag->setTag($superTag);
                    $independantTag->setSuper(true);
                    $independant->addIndependantTag($independantTag);
                }
            }
            $tagsToRemove = [];
            // Suppression des tags qui ne sont plus sélectionnés comme selectedTags ni comme superTags
            foreach ($independant->getIndependantTags() as $independantTag) {
                if (!in_array($independantTag->getTag(), $selectedTags) && !in_array($independantTag->getTag(), $selectedSuperTags)) {
                    $independant->removeIndependantTag($independantTag);
                }
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
            'tags' => null, // Assuming you're passing an array of Tag entities for 'superTags'
            'independant' => null, // Option to pass the Independant entity
            'selected_tags' => [],
        ]);
    }
}
