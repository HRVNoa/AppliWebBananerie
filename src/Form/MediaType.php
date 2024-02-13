<?php

namespace App\Form;

use App\Entity\Carrousel;
use App\Entity\Independant;
use App\Entity\Media;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('alt', TextType::class, [
                'attr' => [
                    'class'=> 'form-control form-control-user',
                ],
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le texte doit comporter au moins {{ limit }} caractères.',
                        'max' => 300,
                        'maxMessage' => 'Le texte comporte plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('lien', FileType::class, [
                'required' => true,
                'data_class' => null,
                'mapped' => false,
                'attr' => [
                    'class'=> 'form-control form-control-user',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '12M',
                    ])
                ],
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
