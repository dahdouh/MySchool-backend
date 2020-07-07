<?php


namespace App\Controller\Student;

use App\Entity\DesktopDocument;
use App\Entity\Event;
use App\Entity\HistoricAction;
use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Service\CurrentMember;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student/desktop")
 */
class DesktopController extends AbstractController
{
    /**
     * @Route("/", name="desktop")
     *
     * @return Response
     *
     * @throws Exception
     */
    public function showDesktop()
    {
        return $this->redirectToRoute("desktop_calendar");
    }

    /**
     * @Route("/calendar", name="desktop_calendar")
     * @param EntityManagerInterface $manager
     *
     * @return Response
     *
     * @throws Exception
     */
    public function showCalendar(EntityManagerInterface $manager)
    {
        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        return $this->render('student/desktop/event/show.html.twig', [
            "member" => $member,
            "nav" => "event"
        ]);
    }

    /**
     * @Route("/document", name="desktop_document")
     * @param EntityManagerInterface $manager
     *
     * @return Response
     *
     * @throws Exception
     */
    public function showDocuments(EntityManagerInterface $manager)
    {
        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        return $this->render('student/desktop/document/show.html.twig', [
            "member" => $member,
            "nav" => "document"
        ]);
    }

    /**
     * @Route("/event/form", name="new_event_form")
     * @Route("/event/{id}/form", name="edit_event_form")
     *
     * @param Event|null $event
     *
     * @return Response
     */
    public function newEventForm(Event $event = null)
    {
        return $this->render('student/desktop/event/create.html.twig', [
            "classes" => Event::getAllClass(),
            "event" => $event
        ]);
    }

    /**
     * @Route("/event/new/save", name="new_event_action")
     * @Route("/event/{id}/save", name="edit_event_action")
     *
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param EntityManagerInterface $manager
     * @param Event $event
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function saveEventAction(Request $request, CurrentMember $currentMember,
                                    EntityManagerInterface $manager, Event $event = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("desktop_calendar"));

        $class = $request->get("class");
        if (is_null(array_search($class, Event::getAllClass()))) {
            throw new Exception("Cette classe d'évènements n'est pas disponible.");
        }

        $flashMessage = "L'évènement a été édité avec succès.";

        if (!$event) { // Creating new event
            $event = new Event();

            $flashMessage = "L'évènement a été créé avec succès.";
            $action->setDescription("Ajout de l'événement sur mon calendrier " .
                $request->get("title"));
        } else {
            $action->setDescription("Modification de l'événement sur mon calendrier " .
                $event->getTitle());
        }

        $event->setTitle($request->get("title"));
        $event->setUrl($request->get("url"));
        $event->setContent($request->get("content"));
        $event->setStartDate(new DateTime($request->get("start_date")));
        $event->setEndDate(new DateTime($request->get("end_date")));
        $event->setClass($request->get("class"));
        $event->setStudent($currentMember->getMember());

        $manager->persist($event);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        return $this->redirectToRoute("desktop_calendar");
    }


    /**
     * @Route("/event/{id}/delete", name="delete_event_action")
     *
     * @param EntityManagerInterface $manager
     * @param Event $event
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deleteEventAction(EntityManagerInterface $manager, Event $event, CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression de l'événement sur mon calendrier " .
            $event->getTitle());

        $manager->remove($event);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'évènement a été supprimé.");

        return $this->redirectToRoute("desktop_calendar");
    }

    /**
     * @Route("/document/form", name="desktop_document_form")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function newDocumentForm(EntityManagerInterface $manager)
    {
        /** @var MemberRepository $memberRepository */
        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        return $this->render('/student/desktop/document/create.html.twig', [
            "member" => $member
        ]);
    }

    /**
     * @Route("/document/new/save", name="new_desktop_document_action")
     * @Route("/document/{id}/edit", name="edit_desktop_document_action")
     *
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param EntityManagerInterface $manager
     * @param DesktopDocument $document
     *
     * @return Response
     *
     */
    public function saveDocumentAction(Request $request, CurrentMember $currentMember,
                                       EntityManagerInterface $manager, DesktopDocument $document = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("desktop_document"));

        $member = $currentMember->getMember();

        if ($request->get("_route") == 'new_desktop_document_action') {
            $document = new DesktopDocument();
            $document->setStudent($member);

            $action->setDescription("Ajout du document sur mon bureau " . $request->get("name"));
        } else {
            $action->setDescription("Modification du document sur mon bureau " . $document->getName());
        }

        // create directory upload/member/{id_member}/ if it doesn't already exist
        FileUploader::createDir("upload/member/", $member->getId());

        // create directory upload/member/{id_member}/ if it doesn't already exist
        FileUploader::createDir("upload/member/" . $member->getId() . "/", "desktopDocument");

        $document->setName($request->get("name"));
        $document->setStudent($member);

        $manager->persist($document);
        $manager->persist($action);
        $manager->flush();

        $error = 0;

        if ($file = $request->files->get("document")) {
            $uploader = new FileUploader(
                $request->files->get("document"),
                $document->getId(),
                $member->getDir(),
                "desktopDocument",
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

        return $this->redirectToRoute("desktop_document_form");
    }

    /**
     * @Route("/document/{id}/delete", name="delete_desktop_document_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param DesktopDocument $document
     *
     * @return Response
     */
    public function deleteDocumentAction(EntityManagerInterface $manager,
                                         CurrentMember $currentMember, DesktopDocument $document)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression du document sur mon bureau " . $document->getName());

        FileUploader::removeFile($document->getDir(), $document->getId());

        $manager->remove($document);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le document a été supprimé avec succès.");

        return $this->redirectToRoute('desktop_document_form');
    }
}