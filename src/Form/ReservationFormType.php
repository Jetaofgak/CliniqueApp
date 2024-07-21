<?php

// src/Form/ReservationType.php

namespace App\Form;
use Proxies\__CG__\App\Entity\Schedule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('starttime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Start Time',
            ])
            ->add('endtime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'End Time',
            ])
            ->add('notes', TextType::class, [
                'required' => false,
                'label' => 'Notes',
            ])
            // Note: Do not add 'room' field here
            ->add('save', SubmitType::class, [
                'label' => 'Reserve',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
