<?php


namespace App\Controller\Student;

use App\Controller\HistoricController;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="student_index")
     *
     * @return Response
     */
    public function index()
    {
        return $this->redirectToRoute("student_profile");
    }

    /**
     * @Route("/profile", name="student_profile")
     *
     * @return Response
     */
    public function showMemberProfile()
    {
        return $this->render('student/profile.html.twig', [
            "nav" => "profile"
        ]);
    }

    /**
     * @Route("/account/confirm", name="student_account_confirm_identity")
     *
     * @return Response
     */
    public function showConfirmIdentity()
    {
        return $this->render('student/confirm_identity.html.twig', [
            "nav" => "account"
        ]);
    }

    /**
     * @Route("/account", name="student_account")
     *
     * @return Response
     */
    public function showAccount()
    {
        return $this->render('student/account.html.twig', [
            "nav" => "account"
        ]);
    }

    /**
     * @Route("/historic/{page}", name="student_historic", requirements={"page" = "\d+"}, defaults={"page"=1}))
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

        return $this->render('student/historic.html.twig', $params);
    }
}