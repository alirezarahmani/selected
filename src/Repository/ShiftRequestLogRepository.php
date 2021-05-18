<?php

namespace App\Repository;

use App\Entity\ShiftRequestLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ShiftRequestLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShiftRequestLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShiftRequestLog[]    findAll()
 * @method ShiftRequestLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShiftRequestLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShiftRequestLog::class);
    }

    // /**
    //  * @return ShiftRequestLog[] Returns an array of ShiftRequestLog objects
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
    public function findOneBySomeField($value): ?ShiftRequestLog
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
