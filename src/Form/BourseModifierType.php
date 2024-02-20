<?php

namespace App\Form;

use App\Entity\Bourse;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BourseModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', NumberType::class, [
                'attr' => ['class' => 'forms-sample form-group form-control'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'attr' => ['class' => 'forms-sample form-group form-control'],
            ])
            ->add('Enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary me-2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bourse::class,
        ]);
    }
}
