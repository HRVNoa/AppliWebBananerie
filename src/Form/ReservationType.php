<?php

namespace App\Form;

use App\Entity\Espace;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class,[
                'attr'=>['class'=>'text-success form-control'],
                'disabled' => false
            ])
            ->add('date', DateType::class,[
                'attr'=>['class'=>'text-success form-control', ],
                'disabled' => true
            ])
            ->add('heureDebut', TimeType::class,[
                'attr'=>['class'=>'text-success form-control'],
                'disabled' => true
            ])
            ->add('heureFin', TimeType::class,[
                'attr'=>['class'=>'text-success form-control'],
                'disabled' => true
            ])
            ->add('espace', EntityType::class, [
                'class' => Espace::class,
                'choice_label' => 'id',
                'attr'=>['style'=>'display:none;'],
                'disabled' => true
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'disabled' => true,
                'attr'=>['style'=>'display:none;'],
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
