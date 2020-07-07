<?php


namespace App\Controller\Admin;

use App\Entity\HistoricAction;
use App\Entity\Level;
use App\Entity\Subject;
use App\Repository\LevelRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/subject")
 */
class SubjectController extends AbstractController
{
    /**
     * @Route("/list", name="admin_subject_list")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function showSubjects(EntityManagerInterface $manager)
    {
        $subjectRep = $manager->getRepository(Subject::class);
        $subjects = $subjectRep->findAll();

        return $this->render('admin/subject/list.html.twig', [
            "nav" => "subject",
            "subjects" => $subjects,
        ]);
    }

    /**
     * @Route("/form", name="new_subject_form")
     * @Route("/{id}/form", name="edit_subject_form")
     *
     * @param EntityManagerInterface $manager
     *
     * @param Subject|null $subject
     * @return Response
     */
    public function newSubjectForm(EntityManagerInterface $manager, Subject $subject = null)
    {
        $levelRep = $manager->getRepository(Level::class);
        $levels = $levelRep->findAll();

        return $this->render('admin/subject/create.html.twig', [
            "subject" => $subject,
            "levels" => $levels
        ]);
    }

    /**
     * @Route("/new/save", name="new_subject_action")
     * @Route("/{id}/save", name="edit_subject_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @param CurrentMember $currentMember
     * @param Subject|null $subject
     * @return Response
     *
     */
    public function saveSubjectAction(Request $request, EntityManagerInterface $manager,
                                      CurrentMember $currentMember, Subject $subject = null)
    {
        $action = new HistoricAction($currentMember);

        if (!$subject) { // Creating new subject
            $subject = new Subject();
            $flashMessage = "La matière a été ajoutée avec succès.";
            $action->setDescription("Ajout de la matière " . $request->get("name"));
        } else {
            $flashMessage = "La matière a été éditée avec succès.";
            $action->setDescription("Ajout de la matière " . $subject->getName());
        }

        $subject->setName($request->get("name"));
        foreach ($subject->getLevels() as $level) {
            $subject->removeLevel($level);
        }

        foreach ($request->get("levels") as $id_level) {
            /** @var LevelRepository $levelRep */
            $levelRep = $manager->getRepository(Level::class);
            $level = $levelRep->find($id_level);

            $subject->addLevel($level);
        }

        $manager->persist($subject);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        return $this->redirectToRoute("admin_subject_list");
    }

    /**
     * @Route("/{id}/delete", name="delete_subject_action")
     *
     * @param EntityManagerInterface $manager
     * @param Subject $subject
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deleteLevelAction(EntityManagerInterface $manager, Subject $subject,
                                      CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression de la matière " . $subject->getName());

        foreach ($subject->getCourses() as $course) {
            $manager->remove($course);
        }

        $manager->remove($subject);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success","La matière a été supprimée avec succès.");

        return $this->redirectToRoute("admin_subject_list");
    }
}