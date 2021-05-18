<?php

namespace App\Repository;

use App\Entity\PeriodStaffResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PeriodStaffResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeriodStaffResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeriodStaffResult[]    findAll()
 * @method PeriodStaffResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodStaffResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeriodStaffResult::class);
    }

    // /**
    //  * @return PeriodStaffResult[] Returns an array of PeriodStaffResult objects
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
    public function findOneBySomeField($value): ?PeriodStaffResult
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
