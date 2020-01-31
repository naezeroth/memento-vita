<?php

namespace App\Form;

use App\Entity\Goal;
use App\Entity\Habit;
use App\Entity\User;
use App\Repository\GoalRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HabitFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
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
            ]);

        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException(
                'The FriendMessageFormType cannot be used without an authenticated user!'
            );
        }
        dump($user->getUsername());
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
            if (null !== $event->getData()->getGoal()) {
                // we don't need to add the friend field because
                // the message will be addressed to a fixed friend
                return;
            }

            $form = $event->getForm();

            $formOptions = [
                'class' => Goal::class,
                'choice_label' => 'name',
                'query_builder' => function (GoalRepository $gr) use ($user) {
                    return $gr->createQueryBuilder('u')
                        ->where('u.userBelongsTo = ?1')
                        ->leftJoin('u.purpose', 'purpose')
                        ->andWhere('purpose.active = ?2')
                        ->setParameter(1, $user)
                        ->setParameter(2, true);
                },
            ];
            $form->add('goal', EntityType::class, $formOptions);
        });
//        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Habit::class,
        ]);
    }
}
