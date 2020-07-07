<?php

namespace App\Controller;

use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/qanda", name="qanda")
     *
     * @return Response
     */
    public function indexQanda()
    {
        return $this->render('qanda/qanda.html.twig');
    }

    /**
     * @Route("/live", name="live")
     *
     * @return Response
     */
    public function indexLive()
    {
        return $this->render('live/live.html.twig');
    }

    /**
     * @Route("/register", name="register")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function register(Request $request, EntityManagerInterface $manager,
                             UserPasswordEncoderInterface $encoder)
    {
        $role = $request->get("role");
        if (is_null(array_search($role, Member::getRoleTypesExceptAdmin()))) {
            throw new Exception("Ce rôle n'est pas disponible.");
        }

        
        $member = new Member();
        $member->setEmail($request->get("email"));
        $member->setDateBirth(new DateTime($request->get("date_birth")));
        $member->setPassword($encoder->encodePassword($member, $request->get("password")));
        $member->setLastName($request->get("lastName"));
        $member->setFirstName($request->get("firstName"));
        $member->setRoles([$request->get("role")]);
        $member->setDateRegistration(new DateTime("now"));

        $manager->persist($member);
        $manager->flush();

        return $this->render('index.html.twig', [
                "registration" => true
            ]);
    }
}