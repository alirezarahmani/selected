<?php

namespace App\Repository;

use App\Entity\BudgetTools;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method BudgetTools|null find($id, $lockMode = null, $lockVersion = null)
 * @method BudgetTools|null findOneBy(array $criteria, array $orderBy = null)
 * @method BudgetTools[]    findAll()
 * @method BudgetTools[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetToolsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetTools::class);
    }

    // /**
    //  * @return BudgetTools[] Returns an array of BudgetTools objects
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
    public function findOneBySomeField($value): ?BudgetTools
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
