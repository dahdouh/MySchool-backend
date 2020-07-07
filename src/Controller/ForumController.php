<?php


namespace App\Controller;

use App\Entity\Forum;
use App\Entity\HistoricAction;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Post;
use App\Entity\Subject;
use App\Entity\Topic;
use App\Repository\ForumRepository;
use App\Repository\LevelRepository;
use App\Repository\PostRepository;
use App\Repository\SubjectRepository;
use App\Repository\TopicRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

/**
 * @Route("/forum")
 */
class ForumController extends AbstractController
{
    /**
     * @Route("/", name="forum_index")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function index(EntityManagerInterface $manager)
    {
        $forumRep = $manager->getRepository(Forum::class);
        $forums = $forumRep->findAll();

        return $this->render('forum/list_forum.html.twig', [
            "forums" => $forums,
        ]);
    }

    /**
     * @Route("/{id}/topic/list/{page}", name="topic_list", requirements={"page" = "\d+"}, defaults={"page"=1})
     *
     * @param EntityManagerInterface $manager
     * @param Forum $forum
     * @param int $page
     * @param Request $request
     *
     * @return Response
     */
    public function showTopics(EntityManagerInterface $manager, Forum $forum, int $page, Request $request)
    {
        $maxPerPage = 15;

        /** @var SubjectRepository $subjectRep */
        $subjectRep = $manager->getRepository(Subject::class);
        $subjects = $subjectRep->findByLevel($forum->getLevel());

        $filters = [];

        if ($levelId = $request->get("levelId")) {
            /** @var LevelRepository $levelRep */
            $levelRep = $manager->getRepository(Level::class);

            $filters["levelId"] = $levelId;
            $subjects = $subjectRep->findByLevel($levelRep->find($levelId));
        }

        if ($subjectId = $request->get("subjectId")) {
            $filters["subjectId"] = $subjectId;
        }

        $filters["levelId"] = $forum->getLevel()->getId();

        /** @var TopicRepository $topicRep */
        $topicRep = $manager->getRepository(Topic::class);
        $topics = $topicRep->findPaginated($page, $maxPerPage, $filters);

        return $this->render('topic/list.html.twig', [
            "nav" => "topic",
            "topics" => $topics,
            "forum" => $forum,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($topics) / $maxPerPage),
                'routeName' => 'topic_list',
                'routeParams' => ["id" => $forum->getId()]
            ]
        ]);
    }

    /**
     * @Route("/topic/form", name="new_topic_form")
     * @Route("/topic/{id}/form", name="edit_topic_form")
     *
     * @param Topic|null $topic
     *
     * @return Response
     */
    public function newTopicForm(Topic $topic = null)
    {
        return $this->render('topic/create.html.twig', [
            "topic" => $topic
        ]);
    }

    /**
     * @Route("/topic/new/save", name="new_topic_action")
     * @Route("/topic/{id}/save", name="edit_topic_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @param Topic|null $topic
     * @param CurrentMember $currentMember
     * @return Response
     *
     * @throws Exception
     */
    public function saveTopicAction(Request $request, EntityManagerInterface $manager,
                                    CurrentMember $currentMember, Topic $topic = null)
    {
        $action = new HistoricAction($currentMember);

        $flashMessage = "Le sujet a été édité avec succès.";

        if (!$topic) {
            $topic = new Topic();
            $flashMessage = "Le sujet a été créé avec succès.";
            $action->setDescription("Création du sujet " . $request->get("title"));
        } else {
            $action->setDescription("Modification du sujet " . $topic->getTitle());
        }

        $topic->setAuthor($currentMember->getMember());

        /** @var SubjectRepository $subjectRep */
        $subjectRep = $manager->getRepository(Subject::class);
        $subject = $subjectRep->find($request->get("subjectId"));

        $topic->setSubject($subject);

        /** @var ForumRepository $forumRepository */
        $forumRepository = $manager->getRepository(Forum::class);
        $forum = $forumRepository->findOneBy(["level" => $request->get("levelId")]);
        $topic->setForum($forum);

        //Title
        $topic->setTitle($request->get("title"));

        //Content
        $topic->setContent($request->get("topicContent"));

        //Date
        $topic->setDate(new DateTime());

        $manager->persist($topic);
        $manager->flush();

        $action->setLink($this->generateUrl("post_list", ["id" => $topic->getId()]));
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute("admin_forum_list");
        }

        if ($this->isGranted("ROLE_TEACHER")) {
            return $this->redirectToRoute("teacher_forum_list");
        }

        return $this->redirectToRoute("student_forum_list");
    }

    /**
     * @Route("/topic/{id}/post/list/{page}", name="post_list", requirements={"page" = "\d+"}, defaults={"page"=1})
     *
     * @param EntityManagerInterface $manager
     * @param Topic $topic
     * @param int $page
     *
     * @return Response
     */
    public function showPosts(EntityManagerInterface $manager, Topic $topic, int $page)
    {
        $maxPerPage = 15;

        // go the the last page by default
        if ($page == 0) {
            $page = ceil(count($topic->getPosts()) / $maxPerPage);
        }

        /** @var PostRepository $postRep */
        $postRep = $manager->getRepository(Post::class);
        $posts = $postRep->findByPaginated($page, $maxPerPage, ["topic" => $topic]);

        return $this->render('forum/list_posts.html.twig', [
            "nav" => "post",
            "topic" => $topic,
            "posts" => $posts,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($posts) / $maxPerPage),
                'routeName' => 'post_list',
                'routeParams' => ["id" => $topic->getId()]
            ]
        ]);
    }

    /**
     * @Route("/topic/{id}/post/new/save", name="new_post_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Topic $topic
     * @param CurrentMember $currentMember
     * @return Response
     *
     * @throws Exception
     */
    public function savePostAction(Request $request, EntityManagerInterface $manager, Topic $topic,
                                   CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Post sur le sujet " . $topic->getTitle());
        $action->setLink($this->generateUrl("post_list", ["id" => $topic->getId()]));

        $flashMessage = "Votre post a été publié avec succès.";

        $post = new Post();

        $post->setAuthor($currentMember->getMember());

        //Topic
        $post->setTopic($topic);

        //Content
        $post->setContent($request->get("contentPost"));

        //Date
        $post->setDate(new DateTime());


        $manager->persist($post);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        return $this->redirectToRoute("post_list", [
            "id" => $topic->getId()
        ]);
    }

    /**
     * @Route("/topic/post/{id}/delete", name="delete_post_action")
     *
     * @param EntityManagerInterface $manager
     * @param Post $post
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deletePostAction(EntityManagerInterface $manager, Post $post, CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression du post sur le sujet " . $post->getTopic()->getTitle());
        $action->setLink($this->generateUrl("post_list", ["id" => $post->getTopic()->getId()]));

        $manager->remove($post);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le post a été supprimé avec succès.");

        return $this->redirectToRoute("post_list", [
            "id" => $post->getTopic()->getId()
        ]);
    }

    /**
     * @Route("/topic/{id}/delete", name="delete_topic_action")
     *
     * @param EntityManagerInterface $manager
     * @param Topic $topic
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deleteTopicAction(EntityManagerInterface $manager, Topic $topic, CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression du sujet " . $topic->getTitle());
        $action->setLink($this->generateUrl("post_list", ["id" => $topic->getId()]));

        $manager->remove($topic);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le sujet a été supprimé avec succès.");

        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute("admin_forum_list");
        }

        if ($this->isGranted("ROLE_TEACHER")) {
            return $this->redirectToRoute("teacher_forum_list");
        }

        return $this->redirectToRoute("student_forum_list");
    }
}