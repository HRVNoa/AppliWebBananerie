<?php

namespace App\Form;

use App\Entity\Independant;
use App\Entity\IndependantTag;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependantTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('super', CheckboxType::class, [
                'label'    => 'Super',
                'required' => false,
            ])
            /*
            ->add('independant', EntityType::class, [
                'class' => Independant::class,
'choice_label' => 'id',
            ])*/
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'libelle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IndependantTag::class,
        ]);
    }
}
