<?php

namespace App\Repository;

use App\Entity\MailsToSend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MailsToSend|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailsToSend|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailsToSend[]    findAll()
 * @method MailsToSend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailsToSendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailsToSend::class);
    }

    // /**
    //  * @return MailsToSend[] Returns an array of MailsToSend objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MailsToSend
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
