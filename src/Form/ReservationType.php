<?php

namespace App\Form;

use App\Entity\Espace;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hours = array_combine(range(9, 18), range(9, 18));

        $builder
            ->add('libelle', TextType::class,[
                'attr'=>['class'=>'text-success form-control'],
                'disabled' => false
            ])
            ->add('date', DateType::class,[
                'attr'=>['class'=>'text-success form-control', ],
            ])
            ->add('heureDebut', HiddenType::class, [
                'attr' => ['class' => 'text-success form-control'],
            ])
            ->add('heureFin', HiddenType::class, [
                'attr' => ['class' => 'text-success form-control'],
            ])
            ->add('espace', EntityType::class, [
                'class' => Espace::class,
                'choice_label' => 'id',
                'attr'=>['style'=>'display:none;'],
                'disabled' => true
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'disabled' => true,
                'attr'=>['style'=>'display:none;'],
            ])
            ->add('matin', SubmitType::class, ['attr' => ['class' => 'btn-sm btn-success btn-fw btnConfirm'],'label' => 'Choisir'])
            ->add('apresmidi', SubmitType::class, ['attr' => ['class' => 'btn-sm btn-success btn-fw btnConfirm'],'label' => 'Choisir'])
            ->add('journee', SubmitType::class, ['attr' => ['class' => 'btn-sm btn-success btn-fw btnConfirm'],'label' => 'Choisir'])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);
    }
    public function validate($data, ExecutionContextInterface $context)
    {

        if ($data->getHeureDebut()+4 > $data->getHeureFin() and $data->getEspace()->getTypeEspace()->getCategorie()->getId() != 3) {
            $context->buildViolation('La réservation doit être au minimum 4h pour cette espace.')
                ->atPath('heureFin')
                ->addViolation();
        }

        $typeEspaceId = $data->getEspace()->getTypeEspace()->getCategorie()->getId();
        $heureDebut = $data->getHeureDebut();
        $heureFin = $data->getHeureFin();

        $creneauValide = (
            ($heureDebut == 9 && $heureFin == 13) ||
            ($heureDebut == 14 && $heureFin == 18) ||
            ($heureDebut == 9 && $heureFin == 18)
        );

        if (!$creneauValide && $typeEspaceId != 3) {
            $context->buildViolation('La réservation doit commencer soit à 9h et finir à 13h, soit commencer à 14h et finir à 18h pour cet espace.')
                ->atPath('heureFin')
                ->addViolation();
        }

    }
}
