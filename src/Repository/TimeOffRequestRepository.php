<?php

namespace App\Repository;

use App\Entity\TimeOffRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TimeOffRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeOffRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeOffRequest[]    findAll()
 * @method TimeOffRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeOffRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeOffRequest::class);
    }

    // /**
    //  * @return TimeOffRequest[] Returns an array of TimeOffRequest objects
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
    public function findOneBySomeField($value): ?TimeOffRequest
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
