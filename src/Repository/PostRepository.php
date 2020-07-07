<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Returns a list of posts filtered, sorted by date and paginated
     *
     * @param int $page
     * @param int $maxPerPage
     * @param null|array $filters
     *
     * @return Paginator
     */
    public function findByPaginated($page, $maxPerPage, $filters = null)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                "La valeur de l'argument $page est incorrecte (valeur : $page)."
            );
        }

        if ($page < 1) {
            throw new NotFoundHttpException("La page demandée n'existe pas");
        }

        if (!is_numeric($maxPerPage)) {
            throw new InvalidArgumentException(
                "La valeur de l'argument $maxPerPage est incorrecte (valeur : $maxPerPage)."
            );
        }

        $query = $this
            ->createQueryBuilder("p")
            ->orderBy("p.date", "ASC");

        foreach ($filters as $k => $v) {
            $query->andWhere("p.$k = :val")->setParameter("val", $v);
        }

        $query = $query->getQuery();

        $first_result = ($page - 1) * $maxPerPage;

        $query->setFirstResult($first_result)->setMaxResults($maxPerPage);

        $paginator = new Paginator($query);

        // 404 page, except for page 1
        if ( ($paginator->count() <= $first_result) && $page != 1) {
            throw new NotFoundHttpException("La page demandée n'existe pas.");
        }

        return $paginator;
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
