<?php


namespace App\Service;

use App\Entity\Level;
use Doctrine\ORM\EntityManagerInterface;

class LevelGetter
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getAll()
    {
        $levelRepository = $this->manager->getRepository(Level::class);
        return $levelRepository->findAll();
    }
}