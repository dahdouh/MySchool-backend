<?php


namespace App\Controller;

use App\Entity\HistoricAction;
use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Service\CurrentMember;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/member")
 */
class MemberController extends AbstractController
{
    /**
     * @Route("/", name="member_index")
     */
    public function index()
    {
       if ($this->isGranted("ROLE_ADMIN")) {
           return $this->redirectToRoute("admin_index");
       } elseif ($this->isGranted("ROLE_STUDENT")) {
           return $this->redirectToRoute("student_index");
       } elseif ($this->isGranted("ROLE_TUTOR")) {
           return $this->redirectToRoute("tutor_index");
       } elseif ($this->isGranted("ROLE_TEACHER")) {
           return $this->redirectToRoute("teacher_index");
       }
    }

    /**
     * @Route("/{id}/profile", name="member_profile")
     *
     * @param Member $member
     *
     * @return Response
     */
    public function memberProfile(Member $member)
    {
        return $this->render('member/profile.html.twig', [
            "member" => $member
        ]);
    }

    /**
     * @Route("/account/verify", name="member_verify_identity_action")
     *
     * @param Request $request
     * @param CurrentMember $currentMember
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param SessionInterface $session
     *
     * @return Response
     */
    public function verifyIdentityAction(Request $request, CurrentMember $currentMember,
                                         UserPasswordEncoderInterface $passwordEncoder,
                                         SessionInterface $session)
    {
        if ($passwordEncoder->isPasswordValid($currentMember->getMember(),
            $request->get("password"))) {

            $session->set("identityConfirmed", true);

            if ($this->isGranted("ROLE_STUDENT")) {
                return $this->redirectToRoute("student_account");
            } elseif ($this->isGranted("ROLE_TUTOR")) {
                return $this->redirectToRoute("tutor_account");
            } elseif ($this->isGranted("ROLE_TEACHER")) {
                return $this->redirectToRoute("teacher_account");
            }

        }

        $this->addFlash("danger", "Mot de passe incorrect");

        if ($this->isGranted("ROLE_STUDENT")) {
            return $this->redirectToRoute("student_account_confirm_identity");
        } elseif ($this->isGranted("ROLE_TUTOR")) {
            return $this->redirectToRoute("tutor_account_confirm_identity");
        } elseif ($this->isGranted("ROLE_TEACHER")) {
            return $this->redirectToRoute("teacher_account_confirm_identity");
        }
    }

    /**
     * @Route("/account/information/save", name="member_information_save_action")
     *
     * @param CurrentMember $currentMember
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     *
     * @throws Exception
     */
    public function saveInformationAction(CurrentMember $currentMember, Request $request,
                                          EntityManagerInterface $manager)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Modification des mes informations");

         $member = $currentMember->getMember();
         $oldEmail = $member->getEmail();
         $member->setFirstName($request->get('firstName'));
         $member->setLastName($request->get('lastName'));
         $member->setEmail($newEmail = $request->get('email'));
         $member->setDateBirth(new DateTime($request->get('date_birth')));
         $manager->persist($member);
         $manager->persist($action);
         $manager->flush();

         if ($oldEmail != $newEmail) {
             return $this->redirectToRoute("app_logout");
         }

        if ($this->isGranted("ROLE_STUDENT")) {
            return $this->redirectToRoute("student_account");
        } elseif ($this->isGranted("ROLE_TUTOR")) {
            return $this->redirectToRoute("tutor_account");
        } elseif ($this->isGranted("ROLE_TEACHER")) {
            return $this->redirectToRoute("teacher_account");
        }
    }

    /**
     * @Route("/account/password/save", name="member_password_save_action")
     *
     * @param CurrentMember $currentMember
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return RedirectResponse
     */
    public function savePasswordAction(CurrentMember $currentMember, Request $request,
                                       EntityManagerInterface $manager,
                                       UserPasswordEncoderInterface $passwordEncoder)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Modification des mon mot de passe");

        $member = $currentMember->getMember();
        $member->setPassword(
            $passwordEncoder->encodePassword(
                $member, $request->get('password'))
        );
        $manager->persist($member);
        $manager->persist($action);
        $manager->flush();

        return $this->redirectToRoute("app_logout");
    }

    /**
     * @Route("/account/delete", name="member_delete_action")
     *
     * @param CurrentMember $currentMember
     * @param EntityManagerInterface $manager
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     */
    public function deleteAccountAction(CurrentMember $currentMember, EntityManagerInterface $manager)
    {
        $action = new HistoricAction($currentMember);
        $action->setDescription("Désactivation du compte de " . $currentMember->getMember()->getEmail());

        /** @var MemberRepository $memberRepository */
        $memberRepository = $manager->getRepository(Member::class);
        $admin = $memberRepository->findAdmin();
        $action->setActor($admin);

        $member = $currentMember->getMember();
        $member->setIsActive(false);
        $manager->persist($member);
        $manager->persist($action);
        $manager->flush();

        return $this->redirectToRoute("app_logout");
    }
}