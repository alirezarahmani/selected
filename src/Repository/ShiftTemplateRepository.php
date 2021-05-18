<?php

namespace App\Repository;

use App\Entity\ShiftTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ShiftTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShiftTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShiftTemplate[]    findAll()
 * @method ShiftTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShiftTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShiftTemplate::class);
    }

    // /**
    //  * @return ShiftTemplate[] Returns an array of ShiftTemplate objects
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
    public function findOneBySomeField($value): ?ShiftTemplate
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
