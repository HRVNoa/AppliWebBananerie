<?php

namespace App\Form;

use App\Entity\Espace;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class,[
                'attr'=>['class'=>'form-control text-white'],
            ])
            ->add('date', DateType::class,[
                'attr'=>['class'=>'text-success form-control text-white', ],
            ])
            ->add('plageHoraire', ChoiceType::class, [
                'choices' => [
                    'Journée' => 'journee',
                    'Matin' => 'matin',
                    'Après-midi' => 'apres-midi',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control text-white'],
            ])
            ->add('espace', EntityType::class, [
                'class' => Espace::class,
                'choice_label' => 'libelle',
                'attr' => ['class' => 'form-control text-white'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'attr' => ['class' => 'form-control text-white'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
