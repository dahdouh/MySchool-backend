<?php

namespace App\Repository;

use App\Entity\HistoricAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method HistoricAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoricAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoricAction[]    findAll()
 * @method HistoricAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoricActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoricAction::class);
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
     * Returns a list of course paginated
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
            ->createQueryBuilder("h")
            ->orderBy("h.date", "DESC")
        ;


        if (!is_null($filters)) {

            if (key_exists("actor", $filters)) {
                $query
                    ->andWhere("h.actor = :actor")
                    ->setParameter("actor", $filters["actor"]);
            }

        }

        return $this->paginate($page, $maxPerPage, $query->getQuery());
    }

    // /**
    //  * @return Historic[] Returns an array of Historic objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Historic
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
