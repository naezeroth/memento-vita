<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Habit;
use App\Entity\Milestone;
use App\Entity\Purpose;
use App\Entity\User;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use function Sodium\add;

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
        $em = $this->getDoctrine()->getManager();
        $purpose = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser(), 'active' => true));
        $tempGoals = $em->getRepository(Goal::class)->findBy(array('userBelongsTo' => $security->getUser()));
        $goals = array();
        foreach($tempGoals as $r){
            if($r->getPurpose()->getActive()){ //Violation of Law of Demeter (refactor required)
                array_push($goals, $r);
            }
        }

        if($purpose != null){
            $purpose = $purpose[0];
        }

        $name = $security->getUser()->getUsername();

        return $this->render('dashboard/index.html.twig', [
            'results' => $goals,
            'name' => $name,
            'purpose' => $purpose
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

            $priorPurposes = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser()));
            foreach($priorPurposes as $p){
                $p->setActive(false);
                $em->persist($p);
            }

            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', 'Added your purpose');
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

        $result = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser(), 'active' => false));
        $purpose = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser(), 'active' => true));
        if($purpose != null){
            $purpose = $purpose[0];
        }

        return $this->render('dashboard/display_purpose.html.twig', [
            'results' => $result,
            'purpose' => $purpose
        ]);
    }

     /**
     * @param Request $request
     * @param Security $security
     * @param Purpose $purpose
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/addgoal/", name="add_goal")
     * @throws \Exception
     */
    public function addGoal(Request $request, Security $security){


        $entity = new Goal();
        $entity->setEndDate(new \DateTime('now'));

        $milestone = new Milestone();
        $milestone->setEndDate(new \DateTime('now'));
        $milestone->setDescription('');
        $milestone->setGoalBelongsTo($entity);

//        $entity->addMilestone($milestone);

        $goalForm = $this->createForm(GoalFormType::class, $entity);
        $goalForm->handleRequest($request);

        if($goalForm->isSubmitted()) {
            dump($entity);
            $em = $this->getDoctrine()->getManager();

            $purpose = $em->getRepository(Purpose::class)->findBy(array('userBelongsTo' => $security->getUser(), 'active' => true));
            $purpose = $purpose[0];

            $entity->setUserBelongsTo($security->getUser());
            $entity->setStartDate(new \DateTime("now"));
            $entity->setPurpose($purpose);

            $em->persist($entity);
            $em->flush();
            $message = "Submitted your goal: " . strtolower($entity->getName()); //'Submitted your goal '.{$entity->getName();};
            $this->addFlash('success', $message);
            return $this->redirectToRoute('dashboard');
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
    public function viewPublicGoals(Request $request, Security $security){

        $em = $this->getDoctrine()->getManager();
//        dump(1+1);

//        $tempResult = $em->getRepository(Goal::class)->findBy(array('public' => true)); //Can refactor to the repository and just call function from there.


        $tempResult = $security->getUser()->getSharedGoals();
//        var_dump($tempResult1);
//        dump($tempResult);
        $result = array();
        foreach($tempResult as $r){
            if($r->getPurpose()->getActive()){ //Violation of Law of Demeter (refactor required)
                array_push($result, $r);
            }
        }
//        dump($result);
//        $object = (object) ['name' => array(), 'goal' => array()];
//
//        foreach($result as $r){
//            $object->name = $r->getUserBelongsTo()->getUsername();
//            $object->goal = $r->getName();
//        }
//
//        dump($object);

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
        $tempResult = $em->getRepository(Goal::class)->findBy(array('userBelongsTo' => $security->getUser()));
        $result = array();
        foreach($tempResult as $r){
            if($r->getPurpose()->getActive()){ //Violation of Law of Demeter (refactor required)
                array_push($result, $r);
            }
        }

        return $this->render('dashboard/display_goals.html.twig', [
            'results' => $result
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @param Purpose $purpose
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/addhabit/", name="add_habit")
     * @throws \Exception
     */
    public function addHabit(Request $request, Security $security){

        $entity = new Habit();
        $entity->setDateStart(new \DateTime('now'));

        $habitForm = $this->createForm(HabitFormType::class, $entity);
        $habitForm->handleRequest($request);

        if($habitForm->isSubmitted()) {
            dump($entity->getRecurrenceCollection());
            $em = $this->getDoctrine()->getManager();
            $entity->setUserBelongsTo($security->getUser());

            dump($entity);
            $entity->getDone();
            dump($entity);

            $em->persist($entity);
            $em->flush();
            $message = "Submitted your habit: " . strtolower($entity->getName());
            $this->addFlash('success', $message);
            return $this->redirectToRoute('show_habits');
        }

        return $this->render('dashboard/habit_add.html.twig', [
            'habitForm' => $habitForm->createView(),
        ]);
    }

//    /**
//     * @param Request $request
//     * @param Security $security
//     * @param Purpose $purpose
//     * @return \Symfony\Component\HttpFoundation\Response
//     * @Route("/{id}/addhabit/", name="add_habit")
//     * @ParamConverter("Goal", class="App\Entity\Goal")
//     * @throws \Exception
//     */
//    public function addHabit(Request $request, Security $security, Goal $goal = null){
//
//        $entity = new Habit();
//
//        $habitForm = $this->createForm(HabitFormType::class, $entity);
//        $habitForm->handleRequest($request);
//
//        if($habitForm->isSubmitted()) {
//            dump($entity->getRecurrenceCollection());
//            $em = $this->getDoctrine()->getManager();
//            $entity->setUserBelongsTo($security->getUser());
//            $entity->setDateStart(new \DateTime("now"));
////            $entity->setGoal($goal);
//
//            dump($entity);
//            $entity->getDone();
//            dump($entity);
//
//            $em->persist($entity);
//            $em->flush();
//            $message = "Submitted your habit: " . strtolower($entity->getName());
//            $this->addFlash('success', $message);
////            return $this->redirectToRoute('dashboard');
//        }
//
//        return $this->render('dashboard/habit_add.html.twig', [
//            'habitForm' => $habitForm->createView(),
//        ]);
//    }

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

    /**
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/showhabits", name="show_habits")
     */
    public function displayHabits(Request $request, Security $security){
        $em = $this->getDoctrine()->getManager();
        $tempResult = $em->getRepository(Habit::class)->findBy(array('userBelongsTo' => $security->getUser()));
        $result = array();
//        $recurrenceResult = array();
        foreach($tempResult as $r){
            if($r->getGoal()->getPurpose()->getActive()){ //Violation of Law of Demeter (refactor required)
                array_push($result, $r);
//                array_push($recurrenceResult, $r->getRecurrenceCollection());
            }
        }

        return $this->render('dashboard/display_habits.html.twig', [
            'results' => $result,
//            'recurrenceResult' => $recurrenceResult
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/tracker", name="show_tracker")
     */
    public function displayTracker(Request $request, Security $security){
        $em = $this->getDoctrine()->getManager();
        $tempResult = $em->getRepository(Habit::class)->findBy(array('userBelongsTo' => $security->getUser()));
        $result = array();
        $recurrenceResult = array();
        foreach($tempResult as $r){
            if($r->getGoal()->getPurpose()->getActive()){ //Violation of Law of Demeter (refactor required)
                array_push($result, $r);
                array_push($recurrenceResult, $r->getRecurrenceCollection());
            }
        }
//        foreach($result as $r){
//            var_dump($r);
//            var_dump($r->getRecurrenceCollection());
//            var_dump($r->getDone());
//        }

        return $this->render('dashboard/display_tracker.html.twig', [
            'results' => $result,
            'recurrenceResult' => $recurrenceResult
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/tracker/post", name="post_tracker")
     */
    public function indexAction(Request $request)
    {
        if($request->isXmlHttpRequest()){
            $habit_id = $request->request->get('habit_id');
            $recurrence_id = $request->request->get('recurrence_id');
            $bool = $request->request->get('bool');

            $entityManager = $this->getDoctrine()->getManager();
            $habit = $entityManager->getRepository(Habit::class)->find($habit_id);
            if (!$habit) {
                throw $this->createNotFoundException(
                    'No habit found for id '.$habit_id
                );
            }
            $habit->setSpecificDone($recurrence_id, $bool);
            $entityManager->flush();
//            var_dump($habit_id);
//            var_dump($recurrence_id);
//            var_dump($bool);
//            $str = $habit_id + " " + $recurrence_id + " "
            //make something curious, get some unbelieveable data
//            $str = strval($request->request->get('habit_id')) . $request->request->get('recurrence_id') . $request->request->get('bool');
            $str = "habit id is " . $habit_id . " recurrence id is " . $recurrence_id . " bool is " . $bool;
            $arrData = ['output' => $str];
            return new JsonResponse($arrData);
        }

        return $this->render('dashboard/display_tracker.html.twig');
    }

//    /**
//     * @param Request $request
//     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
//     * @Route("/tracker/post", name="post_tracker")
//     */
//    public function indexAction(Request $request)
//    {
//
////        if($request->request->get('habit_id') &&
////            $request->request->get('recurrence_id') &&
////                $request->request->get('bool')){
//        $habit_id = $request->request->get('habit_id');
//        $recurrence_id = $request->request->get('recurrence_id');
//        $bool = $request->request->get('bool');
//        var_dump($habit_id);
//        var_dump($recurrence_id);
//        var_dump($bool);
//        $str = strval($habit_id) . " habit id " . strval($recurrence_id) . " recurrence id " . strval($bool) . " bool ";
////            $str = echo('{$habit_id} habit id {$recurrence_id} recurrence id {$bool} bool');
//        //make something curious, get some unbelieveable data
//        $arrData = ['output' => $str];
//        return new JsonResponse($arrData);
////        }
//
//        return $this->render('dashboard/display_tracker.html.twig');
//    }


}

