<?php


namespace App\Controller\Tutor;

use App\Entity\HistoricAction;
use App\Entity\Kinship;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Subject;
use App\Entity\Subscription;
use App\Repository\KinshipRepository;
use App\Repository\LevelRepository;
use App\Repository\MemberRepository;
use App\Repository\SubjectRepository;
use App\Repository\SubscriptionRepository;
use App\Service\CurrentMember;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tutor/subscription")
 */
class SubscriptionController extends AbstractController
{
    /**
     * @Route("/list", name="tutor_subscription_list")
     *
     * @return Response
     */
    public function showSubscription()
    {
        return $this->render('tutor/subscription/list.html.twig', [
            "nav" => "subscription"
        ]);
    }

    /**
     * @Route("/{id}/form", name="tutor_subscription_form", requirements={"id" = "\d+"})
     *
     * @param Member $student
     *
     * @return Response
     */
    public function newSubscriptionForm(Member $student)
    {
        return $this->render('tutor/subscription/create.html.twig', [
            "student" => $student
        ]);
    }

    /**
     * @Route("/{id}/new/save", name="tutor_subscription_new_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Request $request
     * @param Member $student
     *
     * @return Response
     *
     * @throws Exception
     */
    public function newSubscriptionAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                          Request $request, Member $student)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("tutor_subscription_list"));
        $action->setDescription("Souscription d'un nouvel abonnement pour " . $student->getFirstName());

        $subscription = new Subscription();
        $subscription->setStudent($student);
        $subscription->setIsActive(true);

        /** @var LevelRepository $levelRepository */
        $levelRepository = $manager->getRepository(Level::class);
        $subscription->setLevel($levelRepository->find($request->get("levelId")));

        // calculate dates
        $months = $request->get("duration");
        $startDate = new DateTime();
        $endDate = new DateTime();
        $endDate->setTimestamp($startDate->getTimestamp());
        $endDate->add(DateInterval::createFromDateString("+ " . $months . " months"));

        $subscription->setDateStart($startDate);
        $subscription->setDateEnd($endDate);

        // add subjects
        foreach (($subjectIds = $request->get("subjectIds")) as $subjectId) {
            /** @var SubjectRepository $subjectRepository */
            $subjectRepository = $manager->getRepository(Subject::class);
            $subscription->addSubject($subjectRepository->find($subjectId));
        }

        // calculate price
        $totalPrice =
            (count($subjectIds) * $this->getParameter("subscription_subjectPrice")) *
            pow($this->getParameter("subscription_monthlyRate"), $months) *
            $months;

        $subscription->setPrice(number_format($totalPrice, "2", ".", ""));

        $manager->persist($subscription);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le nouvel abonnement de votre enfant est désormais actif");

        return $this->redirectToRoute("tutor_subscription_list");
    }

    /**
     * @Route("/{id}/extend/{duration}", name="tutor_subscription_extend_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param string $duration
     * @param Member $student
     *
     * @return Response
     * @throws Exception
     */
    public function extendSubscriptionAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                             string $duration, Member $student)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("tutor_subscription_list"));

        /** @var SubscriptionRepository $subscriptionRep */
        $subscriptionRep = $manager->getRepository(Subscription::class);
        $subscription = $subscriptionRep->findOneBy([
            "student" => $student,
            "isActive" => true
        ]);

        $newDateEnd = new DateTime();
        $newDateEnd->setTimestamp($subscription->getDateEnd()->getTimestamp());
        $newDateEnd->add(DateInterval::createFromDateString($duration));

        $subscription->setDateEnd($newDateEnd);

        $months = $subscription->getDateStart()->diff($subscription->getDateEnd())->format("%m");

        // calculate price
        $totalPrice =
            (count($subscription->getSubjects()) * $this->getParameter("subscription_subjectPrice")) *
            pow($this->getParameter("subscription_monthlyRate"), $months) *
            $months;

        $totalPrice += $subscription->getPrice();

        $subscription->setPrice(number_format($totalPrice, "2", ".", ""));

        switch ($duration) {
            case "+ 1 week":
                $durationLabel = "d'une semaine";
                break;
            case "+ 1 month":
                $durationLabel = "d'un mois";
                break;
            case "+ 3 months":
                $durationLabel = "de trois mois";
                break;
            case "+ 6 months":
                $durationLabel = "de six mois";
                break;
            case "+ 1 year":
                $durationLabel = "d'un an";
                break;
            default:
                $durationLabel = null;
                break;
        }

        $action->setDescription("Prologation $durationLabel l'abonnement actif de " .
            $student->getFirstName());

        $manager->persist($subscription);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'abonnement courant de votre enfant a été prolongé $durationLabel");

        return $this->redirectToRoute("tutor_subscription_list");
    }

    /**
     * @Route("/{id}/deactivate", name="tutor_subscription_deactivate_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     * @param Member $student
     *
     * @return Response
     */
    public function deactivateSubscriptionAction(EntityManagerInterface $manager, CurrentMember $currentMember,
                                                 Member $student)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("tutor_subscription_list"));
        $action->setDescription("Désactivation de l'abonnement actif de " . $student->getFirstName());

        /** @var SubscriptionRepository $subscriptionRep */
        $subscriptionRep = $manager->getRepository(Subscription::class);
        $subscription = $subscriptionRep->findOneBy([
            "student" => $student,
            "isActive" => true
        ]);

        $subscription->setIsActive(false);
        $manager->persist($subscription);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'abonnement courant de votre enfant a été désactivé");

        return $this->redirectToRoute("tutor_subscription_list");
    }

    /**
     * @Route("/student/form", name="tutor_add_student_form")
     *
     * @return Response
     */
    public function addStudentForm()
    {
        return $this->render('tutor/subscription/add_student.html.twig');
    }

    /**
     * @Route("/student/add", name="tutor_add_student_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function addStudentAction(Request $request, EntityManagerInterface $manager, CurrentMember $currentMember)
    {
        /** @var MemberRepository $memberRepository */
        $memberRepository = $manager->getRepository(Member::class);
        $student = $memberRepository->findOneBy(["email" => $request->get("email")]);

        if (!$student || !$student->hasRole(Member::$ROLE_STUDENT['code'])) {
            $this->addFlash("danger", "Le compte étudiant associé à cette adresse mail n'existe pas");
            return $this->redirectToRoute("tutor_add_student_form");
        }

        /** @var KinshipRepository $kinshipRepository */
        $kinshipRepository = $manager->getRepository(Kinship::class);
        if ($kinshipRepository->findOneBy(["student" => $student, "tutor" => $currentMember->getMember()])) {
            $this->addFlash("danger", "Vous avez déjà associé ce compte étudiant");
            return $this->redirectToRoute("tutor_add_student_form");
        }

        if ($kinshipRepository->findOneBy(["student" => $student])) {
            $this->addFlash("danger", "Ce compte étudiant a déjà été associé à un compte parent");
            return $this->redirectToRoute("tutor_add_student_form");
        }

        $kinship = new Kinship();
        $kinship->setStudent($student);
        $kinship->setTutor($currentMember->getMember());

        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("tutor_subscription_list"));
        $action->setDescription("Association du compte étudiant de " . $student->getFirstName());

        $manager->persist($kinship);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Vous pouvez désormais gérer les abonnements de votre enfant");

        return $this->redirectToRoute("tutor_subscription_list");
    }

    /**
     * @Route("/student/{id}/remove", name="tutor_remove_student_action")
     *
     * @param Member $student
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function removeStudentAction(Member $student, EntityManagerInterface $manager,
                                        CurrentMember $currentMember)
    {
        /** @var KinshipRepository $kinshipRepository */
        $kinshipRepository = $manager->getRepository(Kinship::class);
        $kinship = $kinshipRepository->findOneBy([
            "student" => $student,
            "tutor" => $currentMember->getMember()
        ]);

        $action = new HistoricAction($currentMember);
        $action->setDescription("Dissociation du compte étudiant de " . $student->getFirstName());

        $manager->remove($kinship);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le compte étudiant a été dissocié du vôtre");

        return $this->redirectToRoute("tutor_subscription_list");
    }
}