<?php


namespace App\Service;


use App\Entity\Member;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentMember
{
    private $manager;
    private $user;

    public function __construct(EntityManagerInterface $manager, TokenStorageInterface $tokenStorage)
    {
        $this->manager = $manager;

        if ($tokenStorage->getToken()) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function getMember()
    {
        if ($this->user) {
            /** @var MemberRepository $memberRep */
            $memberRep = $this->manager->getRepository(Member::class);
            return $memberRep->findOneBy(["email" => $this->user->getUsername()]);
        }

        return null;
    }
}