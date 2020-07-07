<?php


namespace App\Controller\Admin;

use App\Entity\Forum;
use App\Entity\HistoricAction;
use App\Entity\Level;
use App\Entity\Subject;
use App\Repository\SubjectRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/level")
 */
class LevelController extends AbstractController
{
    /**
     * @Route("/list", name="admin_level_list")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function showLevels(EntityManagerInterface $manager)
    {
        $levelRep = $manager->getRepository(Level::class);
        $levels = $levelRep->findAll();

        return $this->render('admin/level/list.html.twig', [
            "nav" => "level",
            "levels" => $levels,
        ]);
    }

    /**
     * @Route("/form", name="new_level_form")
     * @Route("/{id}/form", name="edit_level_form")
     *
     * @param EntityManagerInterface $manager
     *
     * @param Level|null $level
     * @return Response
     */
    public function newLevelForm(EntityManagerInterface $manager, Level $level = null)
    {
        $subjectRep = $manager->getRepository(Subject::class);
        $subjects = $subjectRep->findAll();

        return $this->render('admin/level/create.html.twig', [
            "level" => $level,
            "subjects" => $subjects
        ]);
    }

    /**
     * @Route("/new/save", name="new_level_action")
     * @Route("/{id}/save", name="edit_level_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Level|null $level
     * @param CurrentMember $currentMember
     *
     * @return Response
     *
     */
    public function saveLevelAction(Request $request, EntityManagerInterface $manager,
                                    CurrentMember $currentMember, Level $level = null)
    {
        $action = new HistoricAction($currentMember);

        if (!$level) { // Creating new level
            $level = new Level();
            $forum = new Forum();
            $forum->setLevel($level);
            $manager->persist($forum);
            $flashMessage = "Le niveau a été ajouté avec succès.";
            $action->setDescription("Ajout du niveau " . $request->get("name"));
        } else {
            $flashMessage = "Le niveau a été édité avec succès.";
            $action->setDescription("Modification du niveau " . $level->getName());
        }

        $level->setName($request->get("name"));

        foreach ($level->getSubjects() as $subject) {
            $level->removeSubject($subject);
        }

        foreach ($request->get("subjects") as $id_subject) {
            /** @var SubjectRepository $subjectRep */
            $subjectRep = $manager->getRepository(Subject::class);
            $subject = $subjectRep->find($id_subject);

            $level->addSubject($subject);
        }

        $manager->persist($level);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        return $this->redirectToRoute("admin_level_list");
    }

    /**
     * @Route("/{id}/delete", name="delete_level_action")
     *
     * @param EntityManagerInterface $manager
     * @param Level $level
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deleteLevelAction(EntityManagerInterface $manager, Level $level, CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression du niveau " . $level->getName());

        foreach ($level->getCourses() as $course) {
            $manager->remove($course);
        }

        $manager->remove($level);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success","Le niveau a été supprimé avec succès.");

        return $this->redirectToRoute("admin_level_list");
    }
}