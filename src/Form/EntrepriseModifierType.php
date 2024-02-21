<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\SecteurActivite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrepriseModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('nomStructure', TextType::class)
            ->add('adresseStructure', TextType::class)
            ->add('email', TextType::class)
            ->add('fonctionStructure', TextType::class)
            ->add('tel' , TextType::class)
            ->add('secteuractivite', EntityType::class,  [
                'class' => SecteurActivite::class,
                'choice_label' => function ($secteuractivite) {
                    return $secteuractivite->getLibelle();
                },
            ])
        ;
        $builder->add('enregistrer',SubmitType::class, array('label' => 'Modifier entreprise', "attr" => ["class" => "btn btn-primary"]));

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
