<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',TextType::class ,[
                'attr'=>['class'=>"form-control p_input text-white"],
                'data' => $options['email'],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                                'mapped' => false,
                'label' => 'Acceptez les conditions d\'utilisations',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepté nos termes.',
                    ]),
                ],
                'attr'=>['class'=>"form-check-input "],
            ])
            ->add('plainPassword', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'mapped' => false,
                        'attr' => ['autocomplete' => 'new-password'],
                        'first_options' => [
                            'constraints' => [
                                new NotBlank([
                                    'message' => 'Merci de rentrez votre mot de passe',
                                ]),
                                new Length([
                                    'min' => 6,
                                    'minMessage' => 'Votre mot de passe doit au moins contenir {{limit}}',
                                    'max' => 4096,
                                ]),
                            ],
                            'label' => 'Mot de passe',
                            'attr' => ['class'=>"form-control p_input text-white"],
                        ],
                        'second_options' => [
                            'label' => 'Confirmer mot de passe',
                            'attr' => ['class'=>"form-control p_input text-white"],
                        ],
                        'invalid_message' => 'Les mots de passe doivent être les mêmes.',


                ]);

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'email' => '',
        ]);
    }
}
