<?php


namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Level;
use App\Entity\Subject;
use App\Repository\CourseRepository;
use App\Repository\LevelRepository;
use App\Repository\SubjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/course")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/list/{page}", requirements={"page" = "\d+"}, name="admin_course_list", defaults={"page"=1})
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

        if ($state = $request->get("state")) {
            $filters["isValid"] = $state;
        }

        /** @var CourseRepository $courseRep */
        $courseRep = $manager->getRepository(Course::class);
        $courses = $courseRep->findPaginated($page, $maxPerPage, $filters);

        return $this->render('admin/course/list.html.twig', [
            "nav" => "course",
            "courses" => $courses,
            "levels" => $levels,
            "subjects" => $subjects,
            "filters" => $filters,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($courses) / $maxPerPage),
                'routeName' => 'admin_course_list',
                'routeParams' => []
            ]
        ]);
    }
}