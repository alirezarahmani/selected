<?php

namespace App\Repository;

use App\Entity\AllowedTerminalIp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AllowedTerminalIp|null find($id, $lockMode = null, $lockVersion = null)
 * @method AllowedTerminalIp|null findOneBy(array $criteria, array $orderBy = null)
 * @method AllowedTerminalIp[]    findAll()
 * @method AllowedTerminalIp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllowedTerminalIpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AllowedTerminalIp::class);
    }

    // /**
    //  * @return AllowedTerminalIp[] Returns an array of AllowedTerminalIp objects
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
    public function findOneBySomeField($value): ?AllowedTerminalIp
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
