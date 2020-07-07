<?php

namespace App\Repository;

use App\Entity\Level;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Level|null find($id, $lockMode = null, $lockVersion = null)
 * @method Level|null findOneBy(array $criteria, array $orderBy = null)
 * @method Level[]    findAll()
 * @method Level[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Level::class);
    }

    /**
     * @param Subject $subject
     *
     * @return Level[] Returns an array of Level objects filtered by subject
     */
    public function findBySubject(Subject $subject)
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.subjects', 's')
            ->andWhere('s.id = :id_subject')
            ->setParameter('id_subject', $subject->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function saveCustomer($firstName)
    {
        $newCustomer = new Level();

        $newCustomer
            ->setName($firstName);

        $this->manager->persist($newCustomer);
        $this->manager->flush();
    }


    // /**
    //  * @return Level[] Returns an array of Level objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Level
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
