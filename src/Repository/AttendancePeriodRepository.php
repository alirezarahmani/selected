<?php

namespace App\Repository;

use App\Entity\AttendancePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AttendancePeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttendancePeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttendancePeriod[]    findAll()
 * @method AttendancePeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendancePeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendancePeriod::class);
    }

    // /**
    //  * @return AttendancePeriod[] Returns an array of AttendancePeriod objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AttendancePeriod
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
