<?php

namespace App\Form;

use App\Entity\Bourse;
use App\Entity\Metier;
use App\Entity\Paiement;
use App\Entity\Statut;
use App\Entity\Tarif;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaiementModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateAchat', DateType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('entreprise', TextType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('email', TextType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('tel', TextType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('adresse', TextType::class, [
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('tarif', EntityType::class, [
                'class' => Tarif::class,
                'choice_label' => 'quantite',
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('Statut', EntityType::class, [
                'class' => Statut::class,
                'choice_label' => 'libelle',
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'libelle',
                'attr' => [
                    'class' => 'forms-sample form-group form-control',
                ],
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary btn-user btn-block form-control form-control-user'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paiement::class,
        ]);
    }
}
