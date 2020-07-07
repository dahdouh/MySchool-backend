<?php


namespace App\Controller\Course;

use App\Entity\Course;
use App\Entity\HistoricAction;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Subject;
use App\Repository\LevelRepository;
use App\Repository\MemberRepository;
use App\Repository\SubjectRepository;
use App\Service\CurrentMember;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/course")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/{id}/show", name="show_course")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function showCourse(Course $course)
    {
        return $this->render('course/show.html.twig', [
            "course" => $course,
            "nav" => "course"
        ]);
    }

    /**
     * @Route("/form", name="new_course_form")
     * @Route("/{id}/form", name="edit_course_form", requirements={"id"="\d+"})
     *
     * @param EntityManagerInterface $manager
     * @param Course|null $course
     *
     * @return Response
     */
    public function courseForm(EntityManagerInterface $manager, Course $course = null)
    {
        $subjectRepository = $manager->getRepository(Subject::class);
        $levelRepository = $manager->getRepository(Level::class);

        $subjects = $subjectRepository->findAll();
        $levels = $levelRepository->findAll();

        return $this->render('course/create.html.twig', [
            "course" => $course,
            "subjects" => $subjects,
            "levels" => $levels,
            "step" => 1
        ]);
    }

    /**
     * @Route("/new/save", name="new_course_action")
     * @Route("/{id}/save", name="edit_course_action")
     *
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param EntityManagerInterface $manager
     * @param Course|null $course
     *
     * @return Response
     *
     * @throws Exception
     */
    public function saveCourseAction(Request $request, CurrentMember $currentMember,
                                     EntityManagerInterface $manager, Course $course = null)
    {
        $action = new HistoricAction($currentMember);

        /** @var LevelRepository $levelRepository */
        $levelRepository = $manager->getRepository(Level::class);
        /** @var SubjectRepository $subjectRepository */
        $subjectRepository = $manager->getRepository(Subject::class);

        $subject = $subjectRepository->find($request->get("subject"));
        $level = $levelRepository->find($request->get("level"));

        $flashMessage = "Le cours a été édité avec succès.";

        if (!$course) { // Creating new course
            /** @var MemberRepository $memberRepository */
            $memberRepository = $manager->getRepository(Member::class);
            $teacher = $memberRepository->findOneBy(['email' => $this->getUser()->getUsername()]);

            $course = new Course();
            $course->setTeacher($teacher);
            $course->setDateCreation(new DateTime("now"));

            $flashMessage = "Le cours a été créé avec succès.";
            $action->setDescription("Ajout du cours " . $request->get("name"));
        } else {
            $action->setDescription("Modification du cours " . $course->getName());
        }

        $course->setName($request->get("name"));
        $course->setContent($request->get("content"));
        $course->setDescription($request->get("description"));
        $course->setSubject($subject);
        $course->setLevel($level);
        $course->setIsValid(false);
        $course->setDateUpdate(new DateTime("now"));

        $manager->persist($course);
        $manager->flush();

        $action->setLink($this->generateUrl("show_course", ["id" => $course->getId()]));
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        return $this->redirectToRoute("edit_course_form", ["id" => $course->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="delete_course_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Course $course
     *
     * @return Response
     */
    public function deleteCourseAction(EntityManagerInterface $manager, CurrentMember $currentMember, Course $course)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression du cours " . $course->getName());

        FileUploader::removeFile("upload/course", $course->getId());

        $manager->remove($course);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le cours a été supprimé avec succès.");

        /** @var MemberRepository $memberRepository */
        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        if ($member->hasRole('ROLE_ADMIN')) {
            $routeName = "admin_course_list";
        } else {
            $routeName = "teacher_course_list";
        }

        return $this->redirectToRoute($routeName);
    }

    /**
     * @Route("/{id}/validate", name="validate_course_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Course $course
     *
     * @return Response
     */
    public function validateCourseAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                         Course $course)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Validation du cours " . $course->getName());
        $action->setLink($this->generateUrl("show_course", ["id" => $course->getId()]));

        $course->setIsValid(true);

        $manager->persist($course);
        $manager->persist($action);
        $manager->flush();

        return $this->redirectToRoute("admin_course_list");
    }
}