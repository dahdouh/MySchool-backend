<?php

namespace App\Repository;

use App\Entity\CourseContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CourseContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseContent[]    findAll()
 * @method CourseContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseContent::class);
    }

    // /**
    //  * @return CourseContent[] Returns an array of CourseContent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CourseContent
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
