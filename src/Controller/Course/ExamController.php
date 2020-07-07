<?php


namespace App\Controller\Course;

use App\Entity\Course;
use App\Entity\Exam;
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
 * @Route("/course/{id_course}/exam", requirements={"id"="\d+"})
 * @Entity("course", expr="repository.find(id_course)")
 */
class ExamController extends AbstractController
{
    /**
     * @Route("/show", name="show_exam")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function showExam(Course $course)
    {
        return $this->render('course/exam/show.html.twig', [
            "course" => $course,
            "nav" => "exam"
        ]);
    }

    /**
     * @Route("/form", name="exam_form")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function examForm(Course $course)
    {
        return $this->render('course/exam/create.html.twig', [
            "course" => $course,
            "step" => 6
        ]);
    }

    /**
     * @Route("/new/save", name="new_exam_action")
     * @Route("/{id}/edit", name="edit_exam_action")
     *
     * @param EntityManagerInterface $manager
     * @param Course $course
     * @param CurrentMember $currentMember
     * @param Request $request
     * @param Exam|null $exam
     *
     * @return Response
     *
     * @throws Exception
     */
    public function saveExamAction(EntityManagerInterface $manager, Course $course, CurrentMember $currentMember,
                                   Request $request, Exam $exam = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Modification d'une évaluation pour le cours " . $course->getName());
        $action->setLink($this->generateUrl("show_exam", ["id_course" => $course->getId()]));

        $course->setIsValid(false);
        $course->setDateUpdate(new DateTime("now"));

        if ($request->get("_route") == 'new_exam_action') {
            $exam = new Exam();
            $exam->setCourse($course);
            $action->setDescription("Ajout d'une évaluation pour le cours " . $course->getName());
        }

        $exam->setContent($request->get("content"));
        $exam->setCorrection($request->get("correction"));

        $manager->persist($exam);
        $manager->persist($course);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'évaluation a été enregistrée avec succès.");

        return $this->redirectToRoute('exam_form', ["id_course" => $course->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="delete_exam_action")
     *
     * @param EntityManagerInterface $manager
     * @param Course $course
     * @param Exam $exam
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deleteExamAction(EntityManagerInterface $manager, Course $course, Exam $exam,
                                     CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression d'une évaluation pour le cours " . $course->getName());

        $manager->remove($exam);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'évaluation a été supprimée avec succès.");

        return $this->redirectToRoute('exam_form', ["id_course" => $course->getId()]);
    }
}