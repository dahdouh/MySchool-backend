<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Topic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Topic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Topic[]    findAll()
 * @method Topic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    /**
     * Paginate a list of results
     *
     * @param int $page
     * @param int $maxPerPage
     * @param Query $query
     *
     * @return Paginator
     */
    private function paginate(int $page, int $maxPerPage, Query $query) {
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
        $first_result = ($page - 1) * $maxPerPage;

        $query->setFirstResult($first_result)->setMaxResults($maxPerPage);

        $paginator = new Paginator($query);

        // 404 page, except for page 1
        if ( ($paginator->count() <= $first_result) && $page != 1) {
            throw new NotFoundHttpException("La page demandée n'existe pas.");
        }

        return $paginator;
    }

    /**
     * Returns a list of topics paginated
     *
     * @param int $page
     * @param int $maxPerPage
     * @param array|null $filters
     *
     * @return Paginator
     */
    public function findPaginated($page, $maxPerPage, $filters = null)
    {
        $query = $this
            ->createQueryBuilder("t")
            ->join("t.forum", "forum")
            ->join("forum.level", "level")
            ->join("t.subject", "subject")
            ->leftJoin('t.posts','p')
            ->addOrderBy("t.date", "DESC")
            ->addOrderBy("p.date", "DESC")
        ;


        if (!is_null($filters)) {

            if (key_exists("author", $filters)) {
                $query
                    ->andWhere("c.author = :author")
                    ->setParameter("author", $filters["author"]);
            }

            if (key_exists("levelId", $filters)) {
                $query
                    ->andWhere("level.id = :level")
                    ->setParameter("level", $filters["levelId"]);
            }

            if (key_exists("subjectId", $filters)) {
                $query
                    ->andWhere("subject.id = :subject")
                    ->setParameter("subject", $filters["subjectId"]);
            }

            if (key_exists("subjects", $filters)) {
                $ors = [];
                foreach ($filters["subjects"] as $subjectId) {
                    $ors[] = $query->expr()->orx('subject.id = '.$query->expr()->literal($subjectId));
                }

                $query->andWhere(join(' OR ', $ors));
            }
        }

        return $this->paginate($page, $maxPerPage, $query->getQuery());
    }

    // /**
    //  * @return Topic[] Returns an array of Topic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Topic
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
