<?php

namespace App\Repository;

use App\Entity\SwapUserShiftAccept;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SwapUserShiftAccept|null find($id, $lockMode = null, $lockVersion = null)
 * @method SwapUserShiftAccept|null findOneBy(array $criteria, array $orderBy = null)
 * @method SwapUserShiftAccept[]    findAll()
 * @method SwapUserShiftAccept[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SwapUserShiftAcceptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SwapUserShiftAccept::class);
    }

    // /**
    //  * @return SwapUserShiftAccept[] Returns an array of SwapUserShiftAccept objects
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
    public function findOneBySomeField($value): ?SwapUserShiftAccept
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
