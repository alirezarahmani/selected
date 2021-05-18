<?php

namespace App\Repository;

use App\Entity\TimeoffLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TimeoffLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeoffLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeoffLog[]    findAll()
 * @method TimeoffLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeoffLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeoffLog::class);
    }

    // /**
    //  * @return TimeoffLog[] Returns an array of TimeoffLog objects
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
    public function findOneBySomeField($value): ?TimeoffLog
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
