<?php

namespace App\Form;

use App\Entity\Espace;
use App\Entity\TarifEspaceTarif;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarifEspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heure', NumberType::class,[
                'attr' => ['class' => 'form-control'],
                'disabled' => true,
            ])
            ->add('prix', NumberType::class,[
                'attr' => ['class' => 'form-control text-light']
            ])
            ->add('espace', EntityType::class, [
                'class' => Espace::class,
                'choice_label' => 'libelle',
                'disabled' => true,
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TarifEspaceTarif::class,
        ]);
    }
}
