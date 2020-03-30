<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Goal;
use Doctrine\ORM\EntityRepository;
//use http\Client\Curl\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $test = $options['userID'];
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name of your goal',
            ])
            ->add('description', TextType::class, [
                'label' => 'Description of your goal',
            ])
            ->add('endDate',  DateType::class, [
                'label' => 'When do you want to accomplish this by?',
                'years' => range(date("Y"), date("Y")+10)
            ])
            ->add('usersAssociatedTo', EntityType::class, [
                'class' => 'App\Entity\User',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('u');
                    $expr = $qb->expr();
                    $qb->where($expr->neq('u.username', '?1'));
                    $qb->orderBy('u.username', 'ASC');
                    $qb->setParameter(1, ($options['userID']));
                    return $qb;
                },
                'choice_label' => 'username',
                'label' => 'Select who you would like to share this with',
//                'expanded' => 'true', //looks like checkboxes
                'multiple' => 'true'
            ])
            ->add('milestones', CollectionType::class, [
                'entry_type' => MilestoneFormType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                // this allows the creation of new forms and the prototype too
                'allow_add' => true,
                // self explanatory, this one allows the form to be removed
                'allow_delete' => true,
//                'label' => 'Add a milestone metric for your goal'
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Goal::class,
            'userID' => -1
        ]);
    }
}
