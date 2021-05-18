<?php

namespace App\Repository;

use App\Entity\Annotations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Annotations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Annotations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Annotations[]    findAll()
 * @method Annotations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnotationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annotations::class);
    }

    // /**
    //  * @return Annotations[] Returns an array of Annotations objects
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
    public function findOneBySomeField($value): ?Annotations
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
