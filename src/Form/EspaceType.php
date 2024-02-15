<?php

namespace App\Form;

use App\Entity\Carrousel;
use App\Entity\Espace;
use App\Entity\TypeEspace;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class,[
                'attr'=>['class'=>'text-light form-control'],
            ])
            ->add('description', TextareaType::class, [
                'attr'=>['class'=>'text-light form-control','style'=>'height: 100px; '],
            ])
            ->add('carrousel', EntityType::class, [
                'class' => Carrousel::class,
                'choice_label' => 'id',
                'disabled' => true,
                'attr'=>['class'=>'text-light form-control'],
            ])
            ->add('typeEspace', EntityType::class, [
                'class' => TypeEspace::class,
                'choice_label' => 'libelle',
                'attr'=>['class'=>'text-light form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Espace::class,
        ]);
    }
}
