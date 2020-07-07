<?php


namespace App\Controller\Course;

use App\Entity\Answer;
use App\Entity\Choice;
use App\Entity\HistoricAction;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizAttempt;
use App\Repository\ChoiceRepository;
use App\Repository\LevelRepository;
use App\Repository\MemberRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizAttemptRepository;
use App\Repository\QuizRepository;
use App\Service\CurrentMember;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/course/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/subjects", name="subjects_by_level")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function getSubjectsByLevel(Request $request, EntityManagerInterface $manager)
    {
        /** @var LevelRepository $levelRepository */
        $levelRepository = $manager->getRepository(Level::class);
        $level = $levelRepository->find($request->get("id_level"));

        $subjects = [];
        foreach ($level->getSubjects() as $subject) {
            $subjects[] = [
                "id" => $subject->getId(),
                "name" => $subject->getName(),
            ];
        }

        return new JsonResponse([
            'subjects' => $subjects
        ]);
    }

    private function getQuizAttempt(Request $request, EntityManagerInterface $manager) {
        if ($idQuizAttempt = $request->get("quizAttemptId")) {
            /** @var QuizAttemptRepository $quizAttemptRepository */
            $quizAttemptRepository = $manager->getRepository(QuizAttempt::class);
            $quizAttempt = $quizAttemptRepository->find($idQuizAttempt);
        } else {
            /** @var QuizRepository $quizRepository */
            $quizRepository = $manager->getRepository(Quiz::class);
            $quiz = $quizRepository->find($request->get("quizId"));

            /** @var MemberRepository $memberRepository */
            $memberRepository = $manager->getRepository(Member::class);
            $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

            $quizAttempt = new QuizAttempt();
            $quizAttempt->setStudent($member);
            $quizAttempt->setQuiz($quiz);
            $quizAttempt->setDate(new DateTime("now"));
            $manager->persist($quizAttempt);
        }

        return $quizAttempt;
    }

    /**
     * @Route("/quiz/save", name="save_answer_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     *
     * @return Response
     *
     */
    public function saveAnswerAction(Request $request, EntityManagerInterface $manager, CurrentMember $currentMember)
    {
        $quizAttempt = $this->getQuizAttempt($request, $manager);

        /** @var QuestionRepository $questionRepository */
        $questionRepository = $manager->getRepository(Question::class);
        $question = $questionRepository->find($request->get("questionId"));

        /** @var ChoiceRepository $choiceRepository */
        $choiceRepository = $manager->getRepository(Choice::class);
        $choice = $choiceRepository->find($request->get("choiceId"));

        $answer = new Answer();
        $answer->setStudent($quizAttempt->getStudent());
        $answer->setQuestion($question);
        $answer->setChoice($choice);
        $answer->setQuizAttempt($quizAttempt);
        $manager->persist($answer);

        $quizAttempt->addAnswer($answer);
        $manager->persist($quizAttempt);

        $nbRightAnswers = 0;
        foreach ($quizAttempt->getAnswers() as $answer) {
            // 1 correct answer = 1 point
            if ($answer->getChoice()->getIsAnswer()) {
                $nbRightAnswers += 1;
            }
        }

        // total score out of 10
        $score = ($nbRightAnswers * 10) / count($quizAttempt->getQuiz()->getQuestions());

        $quizAttempt->setScore($score);
        $quizAttempt->setIsComplete(true);
        $manager->persist($quizAttempt);

        if (count($quizAttempt->getAnswers()) == count($quizAttempt->getQuiz()->getQuestions())) {
            $action = new HistoricAction($currentMember);
            $action->setDescription("Participation au quiz du cours " .
                $quizAttempt->getQuiz()->getCourse()->getName());
            $action->setLink($this->generateUrl("show_quiz", [
                "id_course" => $quizAttempt->getQuiz()->getCourse()->getId()
            ]));
            $manager->persist($action);
        }

        $manager->flush();

        return new JsonResponse([
            'quizAttemptId' => $quizAttempt->getId(),
            'score' => $quizAttempt->getScore(),
            'nbRightAnswers' => $nbRightAnswers
        ]);
    }

    /**
     * @Route("/quizAttempt/delete", name="delete_quiz_attempt_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     *
     * @throws Exception
     */
    public function deleteQuizAttemptAction(Request $request, EntityManagerInterface $manager)
    {
        $quizAttempt = $this->getQuizAttempt($request, $manager);
        $manager->remove($quizAttempt);
        $manager->flush();

        return new JsonResponse([
            'success' => 1
        ]);
    }
}