<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\Metier;
use App\Entity\Statut;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class , ["attr" => ["class" => "text-white"]])
            ->add('prenom', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('entreprise', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('tel', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('dateNaiss', DateType::class, ["attr" => ["class" => "text-white"]])
            ->add('copos', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('email', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('adresse', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('ville', TextType::class, ["attr" => ["class" => "text-white"]])
            ->add('statut', EntityType::class,  [
                'class' => Statut::class,
                'choice_label' => function ($statut) {
                    return $statut->getLibelle();
                },
                'attr' => ['class' => "text-white"],
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => function ($metier) {
                    return $metier->getLibelle();
                },
                'attr' => ['class' => "text-white"],
            ])

        ;
        $builder->add('enregistrer',SubmitType::class, array('label' => 'Nouveau Independant', "attr" => ["class" => "btn btn-primary"]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
        ]);
    }
}
