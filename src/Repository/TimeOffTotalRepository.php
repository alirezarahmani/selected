<?php

namespace App\Repository;

use App\Entity\TimeOffTotal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TimeOffTotal|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeOffTotal|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeOffTotal[]    findAll()
 * @method TimeOffTotal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeOffTotalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeOffTotal::class);
    }

    // /**
    //  * @return TimeOffTotal[] Returns an array of TimeOffTotal objects
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
    public function findOneBySomeField($value): ?TimeOffTotal
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
