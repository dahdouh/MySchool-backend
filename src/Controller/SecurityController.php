<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        //$user = $this->getUser();
        //var_dump($user); die;

        return $this->render("index.html.twig", [
            'lastUsername' => $lastUsername,
            'loginError' => true
        ]);
    }

     /**
     * @Route("/api/login_check", name="api_login_check", methods={"POST"})
     */
    public function apilogin(AuthenticationUtils $authenticationUtils): Response
    {
        //$lastUsername = $authenticationUtils->getLastUsername();
        //$credentials [] = $this->getCredentials($request);
        //$correct  = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();
        
        /*return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
        */

        return $this->json(['user'=> $this-> getUser() ? $this.getUser()->getId() : null]);
    }
    

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
