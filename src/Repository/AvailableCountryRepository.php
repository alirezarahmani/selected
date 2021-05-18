<?php

namespace App\Repository;

use App\Entity\AvailableCountry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AvailableCountry|null find($id, $lockMode = null, $lockVersion = null)
 * @method AvailableCountry|null findOneBy(array $criteria, array $orderBy = null)
 * @method AvailableCountry[]    findAll()
 * @method AvailableCountry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvailableCountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvailableCountry::class);
    }

    // /**
    //  * @return AvailableCountry[] Returns an array of AvailableCountry objects
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
    public function findOneBySomeField($value): ?AvailableCountry
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
