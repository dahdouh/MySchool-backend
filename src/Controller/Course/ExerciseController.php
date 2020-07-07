<?php


namespace App\Controller\Course;

use App\Entity\Course;
use App\Entity\Exercise;
use App\Entity\HistoricAction;
use App\Service\CurrentMember;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/course/{id_course}/exercise", requirements={"id"="\d+"})
 * @Entity("course", expr="repository.find(id_course)")
 */
class ExerciseController extends AbstractController
{
    /**
     * @Route("/show", name="show_exercise")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function showExercise(Course $course)
    {
        return $this->render('course/exercise/show.html.twig', [
            "course" => $course,
            "nav" => "exercise"
        ]);
    }

    /**
     * @Route("/form", name="exercise_form")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function exerciseForm(Course $course)
    {
        return $this->render('course/exercise/create.html.twig', [
            "course" => $course,
            "step" => 5
        ]);
    }

    /**
     * @Route("/new/save", name="new_exercise_action")
     * @Route("/{id}/edit", name="edit_exercise_action")
     *
     * @param EntityManagerInterface $manager
     * @param Course $course
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param Exercise|null $exercise
     *
     * @return Response
     *
     * @throws Exception
     */
    public function saveExerciseAction(EntityManagerInterface $manager, Course $course,
                                       Request $request, CurrentMember $currentMember, Exercise $exercise = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Modification d'un exercice pour le cours " . $course->getName());
        $action->setLink($this->generateUrl("show_exercise", ["id_course" => $course->getId()]));

        $course->setIsValid(false);
        $course->setDateUpdate(new DateTime("now"));

        if ($request->get("_route") == 'new_exercise_action') {
            $exercise = new Exercise();
            $exercise->setCourse($course);
            $action->setDescription("Ajout d'un exercice pour le cours " . $course->getName());
        }

        $exercise->setContent($request->get("content"));
        $exercise->setCorrection($request->get("correction"));

        $manager->persist($exercise);
        $manager->persist($course);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'exercice a été enregistré avec succès.");

        return $this->redirectToRoute('exercise_form', ["id_course" => $course->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="delete_exercise_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Course $course
     * @param Exercise $exercise
     *
     * @return Response
     */
    public function deleteExerciseAction(EntityManagerInterface $manager, CurrentMember $currentMember, Course $course, Exercise $exercise)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression d'un exercice pour le cours " . $course->getName());

        $manager->remove($exercise);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'exercice a été supprimé avec succès.");

        return $this->redirectToRoute('exercise_form', ["id_course" => $course->getId()]);
    }
}