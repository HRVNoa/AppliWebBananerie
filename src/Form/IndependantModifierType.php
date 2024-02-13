<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\Tag;
use App\Entity\Statut;
use App\Entity\Metier;
use App\Entity\IndependantTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Collections\ArrayCollection;

class IndependantModifierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('entreprise', TextType::class)
            ->add('tel', TextType::class)
            ->add('dateNaiss', DateType::class)
            ->add('copos', TextType::class)
            ->add('email', TextType::class)
            ->add('adresse', TextType::class)
            ->add('ville', TextType::class)
            ->add('description', TextType::class,[
                'required' => false
            ])
            ->add('facebook', TextType::class,[
                'required' => false
            ])
            ->add('youtube', TextType::class,[
                'required' => false
            ])
            ->add('instagram', TextType::class,[
                'required' => false
            ])
            ->add('linkedin', TextType::class,[
                'required' => false
            ])
            ->add('statut', EntityType::class, [
                'class' => Statut::class,
                'choice_label' => 'libelle',
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'libelle',
            ])
            ->add('metierSecondaire', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => function ($metier2nd) {
                    return $metier2nd->getLibelle();
                },
                'required' => true ,
            ])
            ->add('annuaire', CheckboxType::class,[
                'required' => false
            ]);
        $builder->add('enregistrer', SubmitType::class, ['label' => 'Modifier', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Independant::class,
            'tags' => [],
        ]);
    }
}
