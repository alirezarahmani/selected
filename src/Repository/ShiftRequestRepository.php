<?php

namespace App\Repository;

use App\Entity\ShiftRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ShiftRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShiftRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShiftRequest[]    findAll()
 * @method ShiftRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShiftRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShiftRequest::class);
    }

    // /**
    //  * @return ShiftRequest[] Returns an array of ShiftRequest objects
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
    public function findOneBySomeField($value): ?ShiftRequest
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
