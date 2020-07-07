<?php


namespace App\Controller\Student;

use App\Entity\DesktopDocument;
use App\Entity\Event;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/events", name="calendar_get_events")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     *
     * @throws Exception
     */
    public function getEvents(EntityManagerInterface $manager)
    {
        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        $eventRepository = $manager->getRepository(Event::class);
        $events = $eventRepository->findBy(["student" => $member]);

        $array = [];

        foreach ($events as $event){
            $array[] = [
                "id" => $event->getId(),
                "title" => $event->getTitle(),
                "url" => $event->getUrl(),
                "class" => $event->getClass(),
                "description" => $event->getContent(),
                "start" => $event->getStartDate()->getTimestamp() . '000', // Milliseconds
                "end" => $event->getEndDate()->getTimestamp() . '000' // Milliseconds
            ];
        }

        $result = [
            "success" => 1,
            "result" => $array
        ];

        return new Response(json_encode($result));
    }

}