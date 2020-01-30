<?php

namespace App\Form;

use App\Entity\Habit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HabitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
//            ->add('done')
//            ->add('dateStart')
//            ->add('dateUntil')
            ->add('freq', ChoiceType::class, [
                'choices'  => [
                    'Daily' => 0,
                    'Weekly' => 1,
                    'Monthly' => 2,
                    'Yearly' => 3,
                ],
                'label' => 'What frequency would you like?'
            ])
            ->add('interval', NumberType::class, [
                'label' => 'At what frequency interval would you like this?'
            ])
            ->add('count', NumberType::class, [
                'label' => 'How many recurrences would you like?'
            ])
//            ->add('byDay')
//            ->add('byMonthDay')
//            ->add('byYearDay')
//            ->add('byMonth')
//            ->add('goal')
//            ->add('userBelongsTo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Habit::class,
        ]);
    }
}
