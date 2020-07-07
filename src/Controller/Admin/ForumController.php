<?php


namespace App\Controller\Admin;

use App\Entity\Level;
use App\Entity\Subject;
use App\Entity\Topic;
use App\Repository\LevelRepository;
use App\Repository\SubjectRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/forum")
 */
class ForumController extends AbstractController
{
    /**
     * @Route("/list/{page}", requirements={"page" = "\d+"}, name="admin_forum_list", defaults={"page"=1})
     *
     * @param EntityManagerInterface $manager
     * @param int $page
     * @param Request $request
     *
     * @return Response
     */
    public function showTopics(EntityManagerInterface $manager, int $page, Request $request)
    {
        $maxPerPage = 15;

        /** @var LevelRepository $levelRep */
        $levelRep = $manager->getRepository(Level::class);
        $levels = $levelRep->findAll();

        /** @var SubjectRepository $subjectRep */
        $subjectRep = $manager->getRepository(Subject::class);
        $subjects = $subjectRep->findAll();

        $filters = [];

        if ($levelId = $request->get("levelId")) {
            $filters["levelId"] = $levelId;

            $subjects = $subjectRep->findByLevel($levelRep->find($levelId));
        }

        if ($subjectId = $request->get("subjectId")) {
            $filters["subjectId"] = $subjectId;
        }

        /** @var TopicRepository $topicRep */
        $topicRep = $manager->getRepository(Topic::class);
        $topics = $topicRep->findPaginated($page, $maxPerPage, $filters);

        return $this->render('admin/forum/list.html.twig', [
            "nav" => "forum",
            "topics" => $topics,
            "levels" => $levels,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($topics) / $maxPerPage),
                'routeName' => 'admin_forum_list',
                'routeParams' => []
            ]
        ]);
    }
}