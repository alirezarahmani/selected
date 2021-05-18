<?php

namespace App\Repository;

use App\Entity\SelectedTimeGeneralSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SelectedTimeGeneralSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method SelectedTimeGeneralSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method SelectedTimeGeneralSettings[]    findAll()
 * @method SelectedTimeGeneralSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SelectedTimeGeneralSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SelectedTimeGeneralSettings::class);
    }

    // /**
    //  * @return SelectedTimeGeneralSettings[] Returns an array of SelectedTimeGeneralSettings objects
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
    public function findOneBySomeField($value): ?SelectedTimeGeneralSettings
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
