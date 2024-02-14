<?php

namespace App\Form;

use App\Entity\Carrousel;
use App\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lien', FileType::class,[
                'attr'=>['class'=>'text-light form-control'],
            ])
            ->add('alt', TextareaType::class,[
                'attr'=>['class'=>'text-light form-control'],
            ])
            ->add('carrousel', EntityType::class, [
                'class' => Carrousel::class,
                'choice_label' => 'id',
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
