<?php

namespace App\Repository;

use App\Entity\Shift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Shift|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shift|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shift[]    findAll()
 * @method Shift[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shift::class);
    }

    /**
     * @param $user
     * @return int|mixed|string
     * return array of shifts that are eligible or belongs to user
     *
     */
    public function findByUsers($user)
    {
        $queryBuilder= $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.ownerId','u')
             ->leftJoin('s.eligibleOpenShiftUser','e')
             ->andWhere($queryBuilder->expr()->orX($queryBuilder->expr()->eq('u.id',$user->getId()),$queryBuilder->expr()->eq('e.id',$user->getId())));

         return $queryBuilder->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }



    // /**
    //  * @return Shift[] Returns an array of Shift objects
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
    public function findOneBySomeField($value): ?Shift
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
