<?php

namespace App\Repository;

use App\Entity\AttendanceTimesLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AttendanceTimesLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttendanceTimesLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttendanceTimesLog[]    findAll()
 * @method AttendanceTimesLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendanceTimesLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceTimesLog::class);
    }

    // /**
    //  * @return AttendanceTimesLog[] Returns an array of AttendanceTimesLog objects
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
    public function findOneBySomeField($value): ?AttendanceTimesLog
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
