<?php

namespace App\Form;

use App\Entity\Milestone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MilestoneFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Metric you wish to achieve'
            ])
            ->add('endDate', DateType::class,[
                'label' => 'Date you wish to achieve this by',
                'years' => range(date("Y"), date("Y")+10)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Milestone::class,
        ]);
    }
}
