<?php


namespace App\Service;

use App\Entity\Forum;
use Doctrine\ORM\EntityManagerInterface;

class ForumGetter
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getAll()
    {
        $forumRepository = $this->manager->getRepository(Forum::class);
        return $forumRepository->findAll();
    }
}