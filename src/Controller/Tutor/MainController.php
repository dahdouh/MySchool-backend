<?php


namespace App\Controller\Tutor;

use App\Controller\HistoricController;
use App\Entity\Course;
use App\Entity\Member;
use App\Entity\Subject;
use App\Entity\Subscription;
use App\Repository\CourseRepository;
use App\Repository\MemberRepository;
use App\Repository\SubscriptionRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tutor")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="tutor_index")
     *
     * @return Response
     */
    public function index()
    {
        return $this->redirectToRoute("tutor_profile");
    }

    /**
     * @Route("/profile", name="tutor_profile")
     *
     * @return Response
     */
    public function showMemberProfile()
    {
        return $this->render('tutor/profile.html.twig', [
            "nav" => "profile"
        ]);
    }

    /**
     * @Route("/account/confirm", name="tutor_account_confirm_identity")
     *
     * @return Response
     */
    public function showConfirmIdentity()
    {
        return $this->render('tutor/confirm_identity.html.twig', [
            "nav" => "account"
        ]);
    }

    /**
     * @Route("/account", name="tutor_account")
     *
     * @return Response
     */
    public function showAccount()
    {
        return $this->render('tutor/account.html.twig', [
            "nav" => "account"
        ]);
    }

    /**
     * @Route("/historic/{page}", name="tutor_historic", requirements={"page" = "\d+"}, defaults={"page"=1}))
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

        return $this->render('tutor/historic.html.twig', $params);
    }

    /**
     * @Route("/course/list/{page}", name="tutor_student_course_list",
     *     requirements={"page" = "\d+"}, defaults={"page"=1})))
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @param int $page
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function showStudentCourses(EntityManagerInterface $manager, Request $request, int $page,
                                       CurrentMember $currentMember)
    {
        $maxPerPage = 5;

        $courses = [];
        $subjects = [];
        $filters = [];
        $subscription = null;
        $student = null;

        if (count($currentMember->getMember()->getStudents()) > 0) {
            if ($studentId = $request->get("studentId")) {
                /** @var MemberRepository $memberRepository */
                $memberRepository = $manager->getRepository(Member::class);
                $student = $memberRepository->find($studentId);
            } else {
                $student = $currentMember->getMember()->getStudents()->first();
            }

            $activeSubscription = $student->getSubscriptions()->filter(function (Subscription $subscription) {
                return $subscription->getIsActive();
            })->first();

            if ($student && $activeSubscription) {
                $subjects = $activeSubscription->getSubjects();

                $filters = [
                    "isValid" => true,
                    "levelId" => $activeSubscription->getLevel()->getId()
                ];

                if ($subjectId = $request->get("subjectId")) {
                    $filters["subjects"][] = $subjectId;
                } else {
                    /** @var Subject $subject */
                    foreach ($activeSubscription->getSubjects() as $subject) {
                        $filters["subjects"][] = $subject->getId();
                    }
                }

                /** @var CourseRepository $courseRep */
                $courseRep = $manager->getRepository(Course::class);
                $courses = $courseRep->findPaginated($page, $maxPerPage, $filters);
            }
        }

        return $this->render('tutor/course/list.html.twig', [
            "nav" => "course",
            "courses" => $courses,
            "currentStudent" => $student,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($courses) / $maxPerPage),
                'routeName' => 'tutor_student_course_list',
                'routeParams' => []
            ]
        ]);
    }
}