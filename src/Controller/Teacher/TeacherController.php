<?php


namespace App\Controller\Teacher;

use App\Controller\HistoricController;
use App\Entity\Course;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Subject;
use App\Entity\Topic;
use App\Repository\CourseRepository;
use App\Repository\LevelRepository;
use App\Repository\SubjectRepository;
use App\Repository\TopicRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher")
 */
class TeacherController extends AbstractController
{
    /**
     * @Route("/", name="teacher_index")
     *
     * @return Response
     */
    public function index()
    {
        return $this->redirectToRoute("teacher_profile");
    }

    /**
     * @Route("/course/list/{page}", name="teacher_course_list", requirements={"page" = "\d+"}, defaults={"page"=1}))
     *
     * @param EntityManagerInterface $manager
     * @param int $page
     * @param Request $request
     *
     * @return Response
     */
    public function showCourses(EntityManagerInterface $manager, int $page, Request $request)
    {
        $maxPerPage = 5;

        /** @var LevelRepository $levelRep */
        $levelRep = $manager->getRepository(Level::class);
        $levels = $levelRep->findAll();
        $subjects = [];

        $filters = [];

        if ($levelId = $request->get("levelId")) {
            $filters["levelId"] = $levelId;

            /** @var SubjectRepository $subjectRep */
            $subjectRep = $manager->getRepository(Subject::class);
            $subjects = $subjectRep->findByLevel($levelRep->find($levelId));
        }

        if ($subjectId = $request->get("subjectId")) {
            $filters["subjectId"] = $subjectId;
        }

        if ($state = $request->get("state")) {
            $filters["isValid"] = $state;
        }

        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        $filters["teacher"] = $member->getId();

        /** @var CourseRepository $courseRep */
        $courseRep = $manager->getRepository(Course::class);
        $courses = $courseRep->findPaginated($page, $maxPerPage, $filters);

        return $this->render('teacher/course/list.html.twig', [
            "nav" => "course",
            "member" => $member,
            "courses" => $courses,
            "levels" => $levels,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($courses) / $maxPerPage),
                'routeName' => 'teacher_course_list',
                'routeParams' => []
            ]
        ]);
    }

    /**
     * @Route("/profile", name="teacher_profile")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function showMemberProfile(EntityManagerInterface $manager)
    {
        $memberRep = $manager->getRepository(Member::class);
        $member = $memberRep->findOneBy(["email" => $this->getUser()->getUsername()]);

        return $this->render('teacher/profile.html.twig', [
            "nav" => "profile",
            "member" => $member,
        ]);
    }

    /**
     * @Route("/account/confirm", name="teacher_account_confirm_identity")
     *
     * @return Response
     */
    public function showConfirmIdentity()
    {
        return $this->render('teacher/confirm_identity.html.twig', [
            "nav" => "account"
        ]);
    }

    /**
     * @Route("/account", name="teacher_account")
     *
     * @return Response
     */
    public function showAccount()
    {
        return $this->render('teacher/account.html.twig', [
            "nav" => "account"
        ]);
    }

    /**
     * @Route("/historic/{page}", name="teacher_historic", requirements={"page" = "\d+"}, defaults={"page"=1}))
     *
     * @param EntityManagerInterface $manager
     * @param int $page
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param HistoricController $historicController
     *
     * @return Response
     */
    public function showHistoric(EntityManagerInterface $manager, int $page, Request $request,
                                 CurrentMember $currentMember, HistoricController $historicController)
    {
        $params = $historicController->showHistoric($manager, $page, $currentMember,
            $request->attributes->get('_route'));

        return $this->render('teacher/historic.html.twig', $params);
    }

    /**
     * @Route("/forum/list/{page}", requirements={"page" = "\d+"}, name="teacher_forum_list", defaults={"page"=1})
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

        return $this->render('teacher/forum/list.html.twig', [
            "nav" => "forum",
            "topics" => $topics,
            "levels" => $levels,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($topics) / $maxPerPage),
                'routeName' => 'teacher_forum_list',
                'routeParams' => []
            ]
        ]);
    }
}