<?php


namespace App\Controller\Course;


use App\Entity\Course;
use App\Entity\Document;
use App\Entity\HistoricAction;
use App\Service\CurrentMember;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/course/{id_course}/document", requirements={"id"="\d+"})
 * @Entity("course", expr="repository.find(id_course)")
 */
class DocumentController extends AbstractController
{
    /**
     * @Route("/show", name="show_document")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function showDocument(Course $course)
    {
        return $this->render('course/document/show.html.twig', [
            "course" => $course,
            "nav" => "document"
        ]);
    }

    /**
     * @Route("/form", name="document_form")
     *
     * @param Course $course
     *
     * @return Response
     */
    public function documentForm(Course $course)
    {
        return $this->render('course/document/create.html.twig', [
            "course" => $course,
            "step" => 3
        ]);
    }

    /**
     * @Route("/new/save", name="new_document_action")
     * @Route("/{id}/edit", name="edit_document_action")
     *
     * @param EntityManagerInterface $manager
     * @param Course $course
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param Document|null $document
     *
     * @return Response
     *
     * @throws Exception
     */
    public function saveDocumentAction(EntityManagerInterface $manager, Course $course,
                                       Request $request, CurrentMember $currentMember,
                                       Document $document = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("show_document", [
            "id_course" => $course->getId()
        ]));

        $course->setIsValid(false);
        $course->setDateUpdate(new DateTime("now"));

        // create directory upload/course/{id_course}/ if it doesn't already exist
        FileUploader::createDir("upload/course/", $course->getId());

        // create directory upload/course/{id_course}/video/ if it doesn't already exist
        FileUploader::createDir("upload/course/" . $course->getId() . "/", "document");

        if ($request->get("_route") == 'new_document_action') {
            $document = new Document();
            $document->setCourse($course);
            $action->setDescription("Ajout du document " . $request->get("name"));
        } else {
            $action->setDescription("Modification du document " . $document->getName());
        }

        $document->setName($name = $request->get("name"));
        $document->setDescription($request->get("description"));

        $manager->persist($document);
        $manager->persist($course);
        $manager->persist($action);
        $manager->flush();

        $error = 0;

        if ($file = $request->files->get("document")) {
            $uploader = new FileUploader(
                $request->files->get("document"),
                $document->getId(),
                $course->getDir(),
                "document",
                [".pdf", ".txt", ".gif", ".jpeg", ".jpg", ".png", ".svg", ".csv", ".rtf", ".odt", ".md"]
            );

            $error = $uploader->upload();
        }

        // if there is an error during the upload, show a message to the user
        if ($error !== 0) {
            $this->addFlash("warning", "Le document a été enregistré partiellement.");
            $this->addFlash("danger", "Une erreur est survenue lors 
                de l'upload du fichier :<br/><strong>$error</strong>");
        } else {
            $this->addFlash("success", "Le document a été enregistré avec succès.");
        }

        return $this->redirectToRoute('document_form', ["id_course" => $course->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="delete_document_action")
     *
     * @param EntityManagerInterface $manager
     * @param Course $course
     * @param Document $document
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deleteDocumentAction(EntityManagerInterface $manager, Course $course, Document $document,
                                         CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression du document " . $document->getName());

        FileUploader::removeFile($document->getDir(), $document->getId());

        $manager->remove($document);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le document a été supprimé avec succès.");

        return $this->redirectToRoute('document_form', ["id_course" => $course->getId()]);
    }
}