<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Metier;
use App\Entity\Statut;
use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependantModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('entreprise', TextType::class)
            ->add('tel', TextType::class)
            ->add('dateNaiss', DateType::class )
            ->add('copos', TextType::class)
            ->add('email', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('statut', EntityType::class,  [
                'class' => Statut::class,
                'choice_label' => function ($statut) {
                    return $statut->getLibelle();
                },
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => function ($metier) {
                    return $metier->getLibelle();
                },
            ])
            ->add('selectedTags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'mapped' => false, // Ce champ ne sera pas directement mappé à une propriété de l'entité
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check', // Ajoute une classe CSS personnalisée
                    'max-tags' => 10, // Définir une limite de 10 tags maximum
                ],
            ])
        ;
        $builder->add('enregistrer',SubmitType::class, array('label' => 'Modifier Independant', "attr" => ["class" => "btn btn-primary"]));

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $independant = $event->getData();
            $form = $event->getForm();
            $selectedTags = $form->get('selectedTags')->getData();

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
                    $independantTag->setSuper(false); // Définir la valeur par défaut pour 'super'
                    $independant->addIndependantTag($independantTag);
                }
            }

            foreach ($independant->getIndependantTags() as $independantTag) {
                if (!$selectedTags->contains($independantTag->getTag())) {
                    $independant->removeIndependantTag($independantTag);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
        ]);
    }
}
