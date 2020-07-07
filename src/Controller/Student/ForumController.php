<?php


namespace App\Controller\Student;


use App\Entity\Subject;
use App\Entity\Subscription;
use App\Entity\Topic;
use App\Repository\TopicRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student/forum")
 */
class ForumController extends AbstractController
{
    /**
     * @Route("/list/{page}", requirements={"page" = "\d+"}, name="student_forum_list", defaults={"page"=1})
     *
     * @param EntityManagerInterface $manager
     * @param int $page
     * @param Request $request
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function showTopics(EntityManagerInterface $manager, int $page,
                               Request $request, CurrentMember $currentMember)
    {
        $maxPerPage = 15;

        $topics = [];
        $subjects = [];
        $filters = [];

        $activeSubscription = $currentMember->getMember()->getSubscriptions()->filter(
            function (Subscription $subscription) {
                return $subscription->getIsActive();
            })->first();

        if ($activeSubscription) {
            $subjects = $activeSubscription->getSubjects();

            $filters = [
                "levelId" => $activeSubscription->getLevel()->getId()
            ];

            if ($subjectId = $request->get("subjectId")) {
                $filters["subjectId"] = $subjectId;
            }

            if ($subjectId = $request->get("subjectId")) {
                $filters["subjects"][] = $subjectId;
            } else {
                /** @var Subject $subject */
                foreach ($activeSubscription->getSubjects() as $subject) {
                    $filters["subjects"][] = $subject->getId();
                }
            }

            /** @var TopicRepository $topicRep */
            $topicRep = $manager->getRepository(Topic::class);
            $topics = $topicRep->findPaginated($page, $maxPerPage, $filters);
        }

        return $this->render('student/forum/list.html.twig', [
            "nav" => "forum",
            "topics" => $topics,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($topics) / $maxPerPage),
                'routeName' => 'student_forum_list',
                'routeParams' => []
            ]
        ]);
    }
}