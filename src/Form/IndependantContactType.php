<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class IndependantContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [ 'placeholder' => 'Nom'],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le texte doit comporter au moins {{ limit }} caractères.',
                        'max' => 30,
                        'maxMessage' => 'Le texte comporte plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email'],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le texte doit comporter au moins {{ limit }} caractères.',
                        'max' => 100,
                        'maxMessage' => 'Le texte comporte plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [ 'placeholder' => 'Téléphone'],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le texte doit comporter au moins {{ limit }} chiffre.',
                        'max' => 10,
                        'maxMessage' => 'Le texte comporte plus de {{ limit }} chiffre.',
                    ]),
                ],
            ])
            ->add('objet', TextType::class, [
                'label' => 'Objet',
                'attr' => ['placeholder' => 'Objet'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['placeholder' => 'Ecrire votre message'],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le texte doit comporter au moins {{ limit }} caractères.',
                        'max' => 500,
                        'maxMessage' => 'Le texte comporte plus de {{ limit }} caractères.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
