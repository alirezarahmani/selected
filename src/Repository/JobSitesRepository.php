<?php

namespace App\Repository;

use App\Entity\JobSites;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method JobSites|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobSites|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobSites[]    findAll()
 * @method JobSites[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobSitesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobSites::class);
    }

    // /**
    //  * @return JobSites[] Returns an array of JobSites objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JobSites
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
