<?php


namespace App\Controller;

use App\Entity\HistoricAction;
use App\Repository\HistoricActionRepository;
use App\Service\CurrentMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/historic")
 */
class HistoricController extends AbstractController
{
    /**
     * @param EntityManagerInterface $manager
     * @param int $page
     * @param CurrentMember $currentMember
     * @param string $routeName
     *
     * @return array
     */
    public function showHistoric(EntityManagerInterface $manager, int $page, CurrentMember $currentMember,
                                 string $routeName)
    {
        $maxPerPage = 10;

        /** @var HistoricActionRepository $actionRep */
        $actionRep = $manager->getRepository(HistoricAction::class);
        $actions = $actionRep->findPaginated($page, $maxPerPage, [
            "actor" => $currentMember->getMember()
        ]);

        return [
            "nav" => "historic",
            "actions" => $actions,
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($actions) / $maxPerPage),
                'routeName' => $routeName,
                'routeParams' => []
            ]
        ];
    }
}