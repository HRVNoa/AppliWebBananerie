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
use Symfony\Component\Validator\Constraints\Count;

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
                'required' => true,
                'expanded' => true,
                'constraints' => [
                    new Count([
                        'min' => 3,
                        'max' => 10,
                        'minMessage' => 'Veuillez sélectionner au moins 3 tags.',
                        'maxMessage' => 'Vous ne pouvez pas sélectionner plus de 10 tags.',
                    ]),
                ],
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
                'required' => true,
                'expanded' => true,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'max' => 3,
                        'minMessage' => 'Veuillez sélectionner au moins un super-tag.',
                        'maxMessage' => 'Vous ne pouvez pas sélectionner plus de 3 super-tags.',
                    ]),
                ],
            ])
            ->add('enregistrer', SubmitType::class, ['label' => 'Modifier', 'attr' => ['class' => 'btn btn-primary']]);



        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $independant = $event->getData();
            $form = $event->getForm();
            $selectedTags = $form->get('selectedTags')->getData();
            $selectedSuperTags = $form->get('superTags')->getData();

            // Convertir les superTags en un tableau d'identifiants pour faciliter la vérification
            $selectedSuperTagsIds = array_map(function (Tag $tag) {
                return $tag->getId();
            }, $selectedSuperTags);

            // Supprimer les tags qui sont à la fois dans les tags classiques et les super tags
            $selectedTags = array_filter($selectedTags, function (Tag $tag) use ($selectedSuperTagsIds) {
                return !in_array($tag->getId(), $selectedSuperTagsIds);
            });

            // Réinitialiser les tags de l'indépendant pour les reconstruire à partir des sélections
            foreach ($independant->getIndependantTags() as $independantTag) {
                $independant->removeIndependantTag($independantTag);
            }

            // Ajouter les tags sélectionnés comme tags normaux
            foreach ($selectedTags as $tag) {
                $independantTag = new IndependantTag();
                $independantTag->setIndependant($independant);
                $independantTag->setTag($tag);
                $independantTag->setSuper(false);
                $independant->addIndependantTag($independantTag);
            }

            // Ajouter les super tags sélectionnés
            foreach ($selectedSuperTags as $tag) {
                $independantTag = new IndependantTag();
                $independantTag->setIndependant($independant);
                $independantTag->setTag($tag);
                $independantTag->setSuper(true);
                $independant->addIndependantTag($independantTag);
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
