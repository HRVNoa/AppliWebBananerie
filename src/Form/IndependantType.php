<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\Metier;
use App\Entity\Statut;
use App\Entity\Tag;
use Doctrine\DBAL\Types\BooleanType;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('nom', TextType::class )
            ->add('prenom', TextType::class)
            ->add('entreprise', TextType::class)
            ->add('tel', TextType::class)
            ->add('dateNaiss', DateType::class)
            ->add('copos', TextType::class)
            ->add('email', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('statut', EntityType::class,  [
                'class' => Statut::class,
                'choice_label' => function ($statut) {
                    return $statut->getLibelle();
                },

            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => function ($metier) {
                    return $metier->getLibelle();
                },
            ])
            ->add('annuaire', CheckboxType::class, [
                'required' => false
            ]);
        $builder->add('enregistrer',SubmitType::class, array('label' => 'Confirmer inscription', "attr" => ["class" => "btn btn-primary"]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
        ]);
    }
}
