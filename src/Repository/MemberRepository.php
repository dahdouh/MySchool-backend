<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Member) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
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
     * Returns a list of members paginated
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
            ->createQueryBuilder("m")
            ->andWhere('m.roles NOT LIKE :admin_role')
            ->setParameter('admin_role', '%"ROLE_ADMIN"%');

        if (key_exists("roles", $filters)) {
            foreach ($filters["roles"] as $k => $role) {
                if ($k == 0) {
                    $query->andWhere("m.roles LIKE :firstRole")
                        ->setParameter("firstRole", '%"' . $role . '"%');
                } else {
                    $query->orWhere("m.roles LIKE :role$role")
                        ->setParameter("role$role", '%"' . $role . '"%');
                }
            }
        }

        if (key_exists("isActive", $filters)) {
            $query->andWhere("m.isActive = :isActive")->setParameter("isActive", $filters["isActive"]);
        }

        return $this->paginate($page, $maxPerPage, $query->getQuery());
    }

    // /**
    //  * @return Member[] Returns an array of Member objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @return Member|null
     * @throws NonUniqueResultException
     */
    public function findAdmin(): ?Member
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.roles LIKE :role')
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
