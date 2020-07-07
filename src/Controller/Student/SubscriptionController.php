<?php


namespace App\Controller\Student;

use App\Entity\HistoricAction;
use App\Entity\Level;
use App\Entity\Subject;
use App\Entity\Subscription;
use App\Repository\LevelRepository;
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
 * @Route("/student/subscription")
 */
class SubscriptionController extends AbstractController
{
    /**
     * @Route("/", name="student_subscription_list")
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('student/subscription/list.html.twig', [
            "nav" => "subscription"
        ]);
    }

    /**
     * @Route("/form", name="student_subscription_form")
     *
     * @return Response
     */
    public function newSubscriptionForm()
    {
        return $this->render('student/subscription/create.html.twig');
    }

    /**
     * @Route("/new/save", name="student_subscription_new_action")
     *
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @param CurrentMember $currentMember
     *
     * @return Response
     *
     * @throws Exception
     */
    public function newSubscriptionAction(EntityManagerInterface $manager, Request $request,
                                          CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("student_subscription_list"));

        $subscription = new Subscription();
        $subscription->setStudent($currentMember->getMember());
        $subscription->setIsActive(true);

        /** @var LevelRepository $levelRepository */
        $levelRepository = $manager->getRepository(Level::class);
        $subscription->setLevel($level = $levelRepository->find($request->get("levelId")));

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
        $action->setDescription("Souscription d'un nouvel abonnement 
        pour le niveau " . $level->getName());
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Votre nouvel abonnement est désormais actif");

        return $this->redirectToRoute("student_subscription_list");
    }

    /**
     * @Route("/extend/{duration}", name="student_subscription_extend_action")
     *
     * @param EntityManagerInterface $manager
     * @param string $duration
     * @param CurrentMember $currentMember
     *
     * @return Response
     * @throws Exception
     */
    public function extendSubscriptionAction(EntityManagerInterface $manager, string $duration,
                                             CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setLink($this->generateUrl("student_subscription_list"));

        /** @var SubscriptionRepository $subscriptionRep */
        $subscriptionRep = $manager->getRepository(Subscription::class);
        $subscription = $subscriptionRep->findOneBy([
            "student" => $currentMember->getMember(),
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

        $action->setDescription("Prolongation de l'abonnement actif " . $durationLabel);

        $manager->persist($subscription);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'abonnement courant a été prolongé $durationLabel");

        return $this->redirectToRoute("student_subscription_list");
    }

    /**
     * @Route("/deactivate", name="student_subscription_deactivate_action")
     *
     * @param EntityManagerInterface $manager
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deactivateSubscriptionAction(EntityManagerInterface $manager,
                                                 CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Désactivation de l'abonnement actif ");

        /** @var SubscriptionRepository $subscriptionRep */
        $subscriptionRep = $manager->getRepository(Subscription::class);
        $subscription = $subscriptionRep->findOneBy([
            "student" => $currentMember->getMember(),
            "isActive" => true
        ]);

        $subscription->setIsActive(false);
        $manager->persist($subscription);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "L'abonnement courant a été désactivé");

        return $this->redirectToRoute("student_subscription_list");
    }
}