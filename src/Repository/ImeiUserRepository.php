<?php

namespace App\Repository;

use App\Entity\ImeiUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ImeiUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImeiUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImeiUser[]    findAll()
 * @method ImeiUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImeiUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImeiUser::class);
    }

    // /**
    //  * @return ImeiUser[] Returns an array of ImeiUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImeiUser
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
