<?php


namespace App\Controller\Course;

use App\Entity\Course;
use App\Entity\HistoricAction;
use App\Entity\Video;
use App\Service\CurrentMember;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/course/{id_course}/video", requirements={"id"="\d+"})
 * @Entity("course", expr="repository.find(id_course)")
 */
class VideoController extends AbstractController
{
    /**
     * @Route("/show", name="show_video")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function showVideo(Course $course)
    {
        return $this->render('course/video/show.html.twig', [
            "course" => $course,
            "nav" => "video"
        ]);
    }

    /**
     * @Route("/form", name="video_form")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function videoForm(Course $course)
    {
        return $this->render('course/video/create.html.twig', [
            "course" => $course,
            "step" => 2
        ]);
    }

    /**
     * @Route("/new/save", name="new_video_action")
     * @Route("/{id}/edit", name="edit_video_action")
     *
     * @param EntityManagerInterface $manager
     * @param Course $course
     * @param CurrentMember $currentMember
     * @param Request $request
     * @param Video|null $video
     *
     * @return Response
     *
     * @throws Exception
     */
    public function saveVideoAction(EntityManagerInterface $manager, Course $course,
                                    CurrentMember $currentMember,
                                    Request $request, Video $video = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Modification d'une vidéo pour le cours " . $course->getName());
        $action->setLink($this->generateUrl("show_video", ["id_course" => $course->getId()]));

        // create directory upload/course/{id_course}/ if it doesn't already exist
        FileUploader::createDir("upload/course/", $course->getId());

        // create directory upload/course/{id_course}/video/ if it doesn't already exist
        FileUploader::createDir("upload/course/" . $course->getId() . "/", "video");

        $course->setIsValid(false);
        $course->setDateUpdate(new DateTime("now"));

        if ($request->get("_route") == 'new_video_action') {
            $video = new Video();
            $video->setCourse($course);
            $action->setDescription("Ajout d'une vidéo pour le cours " . $course->getName());
        }

        $video->setName($name = $request->get("name"));
        $video->setDescription($request->get("description"));

        $manager->persist($video);
        $manager->persist($course);
        $manager->persist($action);
        $manager->flush();

        $error = 0;

        /** @var UploadedFile $file */
        if ($file = $request->files->get("video")) {
            $uploader = new FileUploader(
                $file,
                $video->getId(),
                $course->getDir(),
                "video",
                [".mp4", ".mkv", ".flv", ".avi", ".wmv"]
            );

            $error = $uploader->upload();
        }

        // if there is an error during the upload, show a message to the user
        if ($error !== 0) {
            $this->addFlash("warning", "La vidéo a été enregistrée partiellement.");
            $this->addFlash("danger", "Une erreur est survenue lors 
                de l'upload du fichier :<br/><strong>$error</strong>");
        } else {
            $this->addFlash("success", "La vidéo a été enregistrée avec succès.");
        }

        return $this->redirectToRoute('video_form', ["id_course" => $course->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="delete_video_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Course $course
     * @param Video $video
     *
     * @return Response
     */
    public function deleteVideoAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                      Course $course, Video $video)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression d'une vidéo pour le cours " . $course->getName());

        FileUploader::removeFile($video->getDir(), $video->getId());

        $manager->remove($video);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "La vidéo a été supprimée avec succès.");

        return $this->redirectToRoute('video_form', ["id_course" => $course->getId()]);
    }
}