<?php

namespace App\Security;

use App\Entity\Member;
use App\Entity\Subscription;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class MemberAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $user = $this->entityManager->getRepository(Member::class)->findOneBy([
            'email' => $credentials['email'],
            "isActive" => true
        ]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    /**
     * Automatically deactivate a subscription that is expired when logging in
     * @param Member $student
     * @throws Exception
     */
    private function checkSubscription(Member $student)
    {
        /** @var Subscription $activeSubscription */
        $activeSubscription = $student->getSubscriptions()->filter(function (Subscription $subscription) {
            return $subscription->getIsActive();
        })->first();

        if ($activeSubscription && $activeSubscription->getDateEnd() < new DateTime()) {
            $activeSubscription->setIsActive(false);
            $this->entityManager->persist($activeSubscription);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse|Response|null
     * @throws Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var Member $member */
        $member = $token->getUser();

        // check subscription of each student of a tutor
        if ($member->hasRole(Member::$ROLE_TUTOR['code'])) {
            foreach ($member->getStudents() as $student) {
                $this->checkSubscription($student);
            }
        }

        // check subscription of a student
        if ($member->hasRole(Member::$ROLE_STUDENT['code'])) {
            $this->checkSubscription($member);
        }

        if ($member->hasRole(Member::$ROLE_ADMIN['code'])) {
            return new RedirectResponse($this->urlGenerator->generate('admin_index'));
        }
        elseif ($member->hasRole(Member::$ROLE_TUTOR['code'])) {
            return new RedirectResponse($this->urlGenerator->generate('tutor_index'));
        }
        elseif ($member->hasRole(Member::$ROLE_STUDENT['code'])) {
            return new RedirectResponse($this->urlGenerator->generate('student_index'));
        }
        elseif ($member->hasRole(Member::$ROLE_TEACHER['code'])) {
            return new RedirectResponse($this->urlGenerator->generate('teacher_index'));
        }

        return new RedirectResponse($this->urlGenerator->generate('index'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }
}
