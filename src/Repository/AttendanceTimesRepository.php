<?php

namespace App\Repository;

use App\Entity\AttendanceTimes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AttendanceTimes|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttendanceTimes|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttendanceTimes[]    findAll()
 * @method AttendanceTimes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendanceTimesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceTimes::class);
    }

    // /**
    //  * @return AttendanceTimes[] Returns an array of AttendanceTimes objects
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
    public function findOneBySomeField($value): ?AttendanceTimes
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
