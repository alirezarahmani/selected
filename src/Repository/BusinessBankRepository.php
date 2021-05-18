<?php

namespace App\Repository;

use App\Entity\BusinessBank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BusinessBank|null find($id, $lockMode = null, $lockVersion = null)
 * @method BusinessBank|null findOneBy(array $criteria, array $orderBy = null)
 * @method BusinessBank[]    findAll()
 * @method BusinessBank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BusinessBankRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessBank::class);
    }

    // /**
    //  * @return BusinessBank[] Returns an array of BusinessBank objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BusinessBank
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
