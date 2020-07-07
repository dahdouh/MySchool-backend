<?php

namespace App\Repository;

use App\Entity\Level;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Subject|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subject|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subject[]    findAll()
 * @method Subject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    /**
     * @param Level $level
     *
     * @return Subject[] Returns an array of Subject objects filtered by level
     */
    public function findByLevel(Level $level)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.levels', 'l')
            ->andWhere('l.id = :id_level')
            ->setParameter('id_level', $level->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Subject[] Returns an array of Subject objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Subject
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
