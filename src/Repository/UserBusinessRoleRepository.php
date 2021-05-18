<?php

namespace App\Repository;

use App\Entity\UserBusinessRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserBusinessRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBusinessRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBusinessRole[]    findAll()
 * @method UserBusinessRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBusinessRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBusinessRole::class);
    }

    // /**
    //  * @return UserBusinessRole[] Returns an array of UserBusinessRole objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserBusinessRole
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
