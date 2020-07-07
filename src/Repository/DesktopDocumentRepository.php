<?php

namespace App\Repository;

use App\Entity\DesktopDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method DesktopDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesktopDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesktopDocument[]    findAll()
 * @method DesktopDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesktopDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesktopDocument::class);
    }

    // /**
    //  * @return DesktopDocument[] Returns an array of DesktopDocument objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DesktopDocument
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
