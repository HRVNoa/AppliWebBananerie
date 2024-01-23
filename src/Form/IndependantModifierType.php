<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\Metier;
use App\Entity\Statut;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependantModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('entreprise')
            ->add('tel')
            ->add('dateNaiss')
            ->add('copos')
            ->add('statut', EntityType::class, [
                'class' => Statut::class,
'choice_label' => 'id',
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
        ]);
    }
}
