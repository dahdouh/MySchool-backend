<?php


namespace App\Controller\Student;


use App\Entity\Course;
use App\Entity\Subject;
use App\Entity\Subscription;
use App\Repository\CourseRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student/course")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/list/{page}", name="student_course_list",
     *     requirements={"page" = "\d+"}, defaults={"page"=1}))
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @param int $page
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function showCourses(EntityManagerInterface $manager, Request $request, int $page,
                                CurrentMember $currentMember)
    {
        $maxPerPage = 5;

        $courses = [];
        $subjects = [];
        $filters = [];
        $subscription = null;

        $activeSubscription = $currentMember->getMember()->getSubscriptions()->filter(
            function (Subscription $subscription) {
                return $subscription->getIsActive();
            })->first();

        if ($activeSubscription) {
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

        return $this->render('student/course/list.html.twig', [
            "nav" => "course",
            "courses" => $courses,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($courses) / $maxPerPage),
                'routeName' => 'student_course_list',
                'routeParams' => []
            ]
        ]);
    }
}