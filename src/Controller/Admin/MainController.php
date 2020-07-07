<?php


namespace App\Controller\Admin;

use App\Controller\HistoricController;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="admin_index")
     *
     * @return Response
     */
    public function index()
    {
        return $this->redirectToRoute("admin_member_list");
    }

    /**
     * @Route("/historic/{page}", name="admin_historic", requirements={"page" = "\d+"}, defaults={"page"=1}))
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

        return $this->render('admin/historic.html.twig', $params);
    }
}