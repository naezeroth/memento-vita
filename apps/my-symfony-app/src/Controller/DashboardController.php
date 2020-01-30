<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Habit;
use App\Entity\Milestone;
use App\Entity\Purpose;
use App\Form\GoalFormType;
use App\Form\HabitFormType;
use App\Form\PurposeFormType;
use App\Repository\PurposeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class DashboardController
 * @package App\Controller
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, Security $security)
    {
        $entity = new Purpose();
        $purposeForm = $this->createForm(PurposeFormType::class, $entity);
        $purposeForm->handleRequest($request);

        if ($purposeForm->isSubmitted()) {
            $entity->setUserBelongsTo($security->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'some info');

        }

        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser()));


        $user = $security->getUser();
        $name = $user->getUsername();

        return $this->render('dashboard/index.html.twig', [
            'purposeForm' => $purposeForm->createView(),
            'results' => $result,
            'name' => $name
        ]);
    }

    /**
     * @Route("/addpurpose", name="add_purpose")
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addPurpose(Request $request, Security $security)
    {
        $entity = new Purpose();
        $purposeForm = $this->createForm(PurposeFormType::class, $entity);
        $purposeForm->handleRequest($request);

        if ($purposeForm->isSubmitted()) {
            $entity->setUserBelongsTo($security->getUser());
            $entity->setActive(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'some info');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/purpose_add.html.twig', [
            'purposeForm' => $purposeForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}/editpurpose", name="edit_purpose")
     * @param Request $request
     * @param Security $security
     * @ParamConverter("purpose", class="App\Entity\Purpose")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editPurpose(Request $request, Security $security, Purpose $purpose)
    {
        $purposeForm = $this->createForm(PurposeFormType::class, $purpose);
        $purposeForm->handleRequest($request);

        if ($purposeForm->isSubmitted()) {
//            $entity->setUserBelongsTo($security->getUser());
//            $entity->setActive(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($purpose);
            $em->flush();
            $this->addFlash('success', 'Modified your purpose!');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/edit_purpose.html.twig', [
            'purposeForm' => $purposeForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/show", name="show_purpose")
     */
    public function displayPurpose(Request $request, Security $security)
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser()));

        return $this->render('dashboard/display_purpose.html.twig', [
            'results' => $result
        ]);
    }

     /**
     * @param Request $request
     * @param Security $security
     * @param Purpose $purpose
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/addgoal/", name="add_goal")
     * @ParamConverter("purpose", class="App\Entity\Purpose")
     * @throws \Exception
     */
    public function addGoal(Request $request, Security $security, Purpose $purpose = null){

        $entity = new Goal();

        $milestone = new Milestone();
        $milestone->setEndDate(new \DateTime('now'));
        $milestone->setDescription('');
        $milestone->setGoalBelongsTo($entity);

        $entity->addMilestone($milestone);

        $goalForm = $this->createForm(GoalFormType::class, $entity);
        $goalForm->handleRequest($request);

        if($goalForm->isSubmitted()) {
            dump($entity);
            $em = $this->getDoctrine()->getManager();
            $entity->setUserBelongsTo($security->getUser());
            $entity->setStartDate(new \DateTime("now"));
            $entity->setPurpose($purpose);

            $em->persist($entity);
            $em->flush();
            $message = "Submitted your goal: " . strtolower($entity->getName()); //'Submitted your goal '.{$entity->getName();};
            $this->addFlash('success', $message);
//            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/goal_add_dynamic.html.twig', [
            'goalForm' => $goalForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @param Purpose $purpose
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/editgoal/", name="edit_goal")
     * @ParamConverter("Goal", class="App\Entity\Goal")
     * @throws \Exception
     */
    public function editGoal(Request $request, Security $security, Goal $goal){

        $goalForm = $this->createForm(GoalFormType::class, $goal);
        $goalForm->handleRequest($request);

        $originalMilestones = new ArrayCollection();

        // Create an ArrayCollection of the current Milestone objects in the database
        foreach ($goal->getMilestones() as $m) {
            $originalMilestones->add($m);
        }

        if($goalForm->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($originalMilestones as $m) {
                if (false === $goal->getMilestones()->contains($m)) {
                    // remove the Task from the Tag
                    $goal->getMilestones()->removeElement($m);
                    $m->setGoalBelongsTo(null);
                    $em->remove($m);
                }
            }
            $em->persist($goal);
            $em->flush();
            $message = "Edited your goal: " . strtolower($goal->getName()); //'Submitted your goal '.{$entity->getName();};
            $this->addFlash('success', $message);
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/edit_goal.html.twig', [
            'goalForm' => $goalForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @Route("/viewPublic", name="view_public")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewPublicGoals(Request $request){

        $em = $this->getDoctrine()->getManager();

        $result = $em->getRepository(Goal::class)->findBy(array('public' => true)); //Can refactor to the repository and just call function from there.

        dump($result);
        $object = (object) ['name' => array(), 'goal' => array()];

        foreach($result as $r){
            $object->name = $r->getUserBelongsTo()->getUsername();
            $object->goal = $r->getName();
        }

        dump($object);

        return $this->render('dashboard/view_public.html.twig', [
            'results' => $result
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/showGoals", name="show_goals")
     */
    public function displayGoal(Request $request, Security $security){
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Goal::class)->findBy(array('userBelongsTo' => $security->getUser()));
        return $this->render('dashboard/display_goals.html.twig', [
            'results' => $result
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @param Purpose $purpose
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/addhabit/", name="add_habit")
     * @ParamConverter("Goal", class="App\Entity\Goal")
     * @throws \Exception
     */
    public function addHabit(Request $request, Security $security, Goal $goal = null){

        $entity = new Habit();

        $habitForm = $this->createForm(HabitFormType::class, $entity);
        $habitForm->handleRequest($request);

        if($habitForm->isSubmitted()) {
            dump($entity->getRecurrenceCollection());
            $em = $this->getDoctrine()->getManager();
            $entity->setUserBelongsTo($security->getUser());
            $entity->setDateStart(new \DateTime("now"));
            $entity->setGoal($goal);

            dump($entity);
            $entity->getDone();
            dump($entity);

            $em->persist($entity);
            $em->flush();
            $message = "Submitted your habit: " . strtolower($entity->getName());
            $this->addFlash('success', $message);
//            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/habit_add.html.twig', [
            'habitForm' => $habitForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @param Purpose $purpose
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/edithabit/", name="edit_habit")
     * @ParamConverter("Habit", class="App\Entity\Habit")
     * @throws \Exception
     */
    public function editHabit(Habit $habit, Request $request, Security $security){
        $form = $this->createForm(HabitFormType::class, $habit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($habit);
            $em->flush();
            $this->addFlash('success', 'Habit Edited! Knowledge is power!');
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('dashboard/edit_habit.html.twig', [
            'habitForm' => $form->createView()
        ]);
    }
}

