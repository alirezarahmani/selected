<?php

namespace App\Repository;

use App\Entity\BusinessCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BusinessCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BusinessCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BusinessCategory[]    findAll()
 * @method BusinessCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BusinessCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessCategory::class);
    }

    // /**
    //  * @return BusinessCategory[] Returns an array of BusinessCategory objects
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
    public function findOneBySomeField($value): ?BusinessCategory
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
