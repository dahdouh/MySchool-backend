<?php


namespace App\Controller\Course;

use App\Entity\Choice;
use App\Entity\Course;
use App\Entity\HistoricAction;
use App\Entity\Member;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Repository\MemberRepository;
use App\Repository\QuestionRepository;
use App\Service\CurrentMember;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/course/{id_course}/quiz", requirements={"id"="\d+"})
 * @Entity("course", expr="repository.find(id_course)")
 */
class QuizController extends AbstractController
{
    /**
     * @Route("/show", name="show_quiz")
     *
     * @param Course $course
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function showQuiz(Course $course, EntityManagerInterface $manager)
    {
        /** @var MemberRepository $memberRepository */
        $memberRepository = $manager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(["email" => $this->getUser()->getUsername()]);

        return $this->render('course/quiz/show.html.twig', [
            "course" => $course,
            "quizAttempts" => $member->getQuizAttempts(),
            "nav" => "quiz"
        ]);
    }

    /**
     * @Route("/form", name="quiz_form")
     *
     * @param Course $course
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function quizForm(Course $course, EntityManagerInterface $manager)
    {
        return $this->render('course/quiz/create.html.twig', [
            "course" => $course,
            "step" => 4
        ]);
    }

    /**
     * @Route("/new/save", name="new_quiz_action")
     * @Route("/{id}/edit", name="edit_quiz_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Course $course
     * @param Request $request
     * @param Quiz|null $quiz
     * @return Response
     *
     * @throws Exception
     */
    public function saveQuizAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                   Course $course, Request $request, Quiz $quiz = null)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Modification d'un quiz pour le cours " . $course->getName());
        $action->setLink($this->generateUrl("show_quiz", ["id_course" => $course->getId()]));

        $course->setIsValid(false);
        $course->setDateUpdate(new DateTime("now"));
        $manager->persist($course);

        // new
        if ($request->get("_route") == 'new_quiz_action') {
            $action->setDescription("Ajout d'un quiz pour le cours " . $course->getName());

            $quiz = new Quiz();
            $quiz->setCourse($course);
            $manager->persist($quiz);

            foreach (array_values(array_filter($request->get("questionContent"))) as $k => $questionContent) {
                $question = new Question();
                $question->setQuiz($quiz);
                $question->setContent($questionContent);
                $manager->persist($question);

                $rightAnswer = new Choice();
                $rightAnswer->setQuestion($question);
                $rightAnswer->setContent(array_values(array_filter($request->get("rightAnswerContent")))[$k]);
                $rightAnswer->setIsAnswer(true);
                $manager->persist($rightAnswer);

                foreach (array_values(array_filter($request->get("choice")[$k])) as $choiceContent) {
                    $choice = new Choice();
                    $choice->setQuestion($question);
                    $choice->setContent($choiceContent);
                    $choice->setIsAnswer(false);
                    $manager->persist($choice);
                }
            }
        }
        // edit
        else {

            // remove questions that have been deleted
            if ($request->get("deletedQuestionsId") != "") {
                $deletedQuestionsId = explode(",", $request->get("deletedQuestionsId"));
                /** @var QuestionRepository $questionRep */
                $questionRep = $manager->getRepository(Question::class);
                foreach ($deletedQuestionsId as $questionId) {
                    if ($question = $questionRep->find($questionId)) {
                        $manager->remove($question);
                    }
                }
            }

            // remove choices that have been deleted
            if ($request->get("deletedChoicesId") != "") {
                $deletedChoicesId = explode(",", $request->get("deletedChoicesId"));
                $choiceRep = $manager->getRepository(Choice::class);
                foreach ($deletedChoicesId as $choiceId) {
                    if ($choice = $choiceRep->find($choiceId)) {
                        $manager->remove($choice);
                    }
                }
            }

            $manager->flush();

            foreach (array_values(array_filter($request->get("questionContent"))) as $q => $questionContent) {
                if (!($question = $quiz->getQuestions()->get($q))) {
                    $question = new Question();
                    $question->setQuiz($quiz);
                }
                $question->setContent($questionContent);
                $manager->persist($question);

                /** @var Choice[] $choices */
                $choices = $question->getChoices()->filter(function(Choice $choice) {
                    return !$choice->getIsAnswer();
                })->getValues();

                $choicesContent = array_values(array_filter($request->get("choice")[$q]));

                foreach ($choices as $c => $choice) {
                    $choice->setContent($choicesContent[$c]);
                    $manager->persist($choice);
                }

                $offset = count($choices);
                $limit = $offset + (count($choicesContent) - $offset);
                for ($c = count($choices); $c < $limit; $c++) {
                    $choice = new Choice();
                    $choice->setQuestion($question);
                    $choice->setIsAnswer(false);
                    $choice->setContent($choicesContent[$c]);
                    $manager->persist($choice);
                }

                /** @var Choice[] $rightAnswerArray */
                $rightAnswerArray = $question->getChoices()->filter(function(Choice $choice) {
                    return $choice->getIsAnswer();
                })->getValues();

                if (!key_exists(0, $rightAnswerArray)) {
                    $rightAnswer = new Choice();
                    $rightAnswer->setQuestion($question);
                } else {
                    $rightAnswer = $rightAnswerArray[0];
                }

                $rightAnswer->setIsAnswer(true);
                $rightAnswer->setContent(array_values(array_filter($request->get("rightAnswerContent")))[$q]);
                $manager->persist($rightAnswer);
            }
        }

        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le quiz a été enregistré avec succès.");

        return $this->redirectToRoute('quiz_form', ["id_course" => $course->getId()]);
    }

    /**
     * @Route("/{id}/delete", name="delete_quiz_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Course $course
     * @param Quiz $quiz
     *
     * @return Response
     */
    public function deleteQuizAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                     Course $course, Quiz $quiz)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Suppression d'un quiz pour le cours " . $course->getName());

        $manager->remove($quiz);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le quiz a été supprimé avec succès.");

        return $this->redirectToRoute('quiz_form', ["id_course" => $course->getId()]);
    }
}