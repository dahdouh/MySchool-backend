<?php


namespace App\Controller\Admin;

use App\Entity\HistoricAction;
use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Service\CurrentMember;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin/member")
 */
class MemberController extends AbstractController
{
    /**
     * @Route("/list/{page}", requirements={"page" = "\d+"}, name="admin_member_list", defaults={"page"=1})
     *
     * @param EntityManagerInterface $manager
     * @param int $page
     *
     * @param Request $request
     * @return Response
     */
    public function showMembers(EntityManagerInterface $manager, int $page, Request $request)
    {
        $maxPerPage = 15;

        $filters = [];

        if ($states = $request->get("states")) {
            if (count($states) == 1) {
                $filters["isActive"] = ($states[0] == "active");
            }
        } elseif ($isActive = $request->get("isActive")) {
            $filters["isActive"] = $isActive;
        }

        if ($roles = $request->get("roles")) {
            unset($filters["roles"]);
            foreach ($roles as $role) {
                $filters["roles"][] = $role;
            }
        }

        /** @var MemberRepository $memberRep */
        $memberRep = $manager->getRepository(Member::class);
        $members = $memberRep->findPaginated($page, $maxPerPage, $filters);

        return $this->render('admin/member/list.html.twig', [
            "nav" => "member",
            "members" => $members,
            "filters" => $filters,
            "roles" => Member::getRoleTypesExceptAdmin(),
            "pagination" => [
                'page' => $page,
                'pagesNumber' => ceil(count($members) / $maxPerPage),
                'routeName' => 'admin_member_list',
                'routeParams' => []
            ]
        ]);
    }

    /**
     * @Route("/form", name="new_member_form")
     * @Route("/{id}/form", name="edit_member_form")
     *
     * @param Member|null $member
     *
     * @return Response
     */
    public function newMemberForm(Member $member = null)
    {
        return $this->render('admin/member/create.html.twig', [
            "roles" => Member::getRoleTypesExceptAdmin(),
            "member" => $member
        ]);
    }

    /**
     * @Route("/new/save", name="new_member_action")
     * @Route("/{id}/save", name="edit_member_action")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     * @param CurrentMember $currentMember
     * @param Member $member
     * @return Response
     *
     * @throws \Exception
     */
    public function saveMemberAction(Request $request,
                                     EntityManagerInterface $manager,
                                     UserPasswordEncoderInterface $encoder, CurrentMember $currentMember,
                                     Member $member = null)
    {
        $action = new HistoricAction($currentMember);

        $role = $request->get("role");
        if (is_null(array_search($role, Member::getRoleTypesExceptAdmin()))) {
            throw new Exception("Ce rôle n'est pas disponible.");
        }

        if (!$member) { // Creating new member
            $member = new Member();
            $member->setDateRegistration(new DateTime());
            $member->setPassword($encoder->encodePassword($member, "123"));
            $action->setDescription("Ajout du membre " . $request->get("email"));

            $flashMessage = "Le membre a été ajouté avec succès.";
        } else {
            $flashMessage = "Le membre a été édité avec succès.";
            $action->setDescription("Modification du membre " . $member->getEmail());
        }

        $member->setEmail($request->get("email"));
        $member->setLastName($request->get("lastName"));
        $member->setFirstName($request->get("firstName"));
        $member->setRoles([$request->get("role")]);
        $member->setDateBirth(new DateTime($request->get("date_birth")));

        $manager->persist($member);
        $manager->persist($action);
        $manager->flush();

        $action->setLink($this->generateUrl("member_profile", ["id" => $member->getId()]));
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", $flashMessage);

        return $this->redirectToRoute("admin_index");
    }

    /**
     * @Route("/{id}/deactivate", name="deactivate_member_action")
     *
     * @param EntityManagerInterface $manager
     * @param Member $member
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function deactivateMemberAction(EntityManagerInterface $manager, Member $member,
                                           CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Désactivation du compte de " . $member->getEmail());

        $member->setIsActive(false);
        $manager->persist($member);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le membre a été désactivé avec succès.");

        return $this->redirectToRoute("admin_index");
    }

    /**
     * @Route("/{id}/activate", name="activate_member_action")
     *
     * @param EntityManagerInterface $manager
     * @param Member $member
     * @param CurrentMember $currentMember
     *
     * @return Response
     */
    public function activateMemberAction(EntityManagerInterface $manager, Member $member,
                                         CurrentMember $currentMember)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Réactivation du compte de " . $member->getEmail());

        $member->setIsActive(true);
        $manager->persist($member);
        $manager->persist($action);
        $manager->flush();

        $this->addFlash("success", "Le membre a été réactivé avec succès.");

        return $this->redirectToRoute("admin_index");
    }
}