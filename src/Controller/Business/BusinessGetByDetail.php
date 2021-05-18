<?php


namespace App\Controller\Business;


use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

class BusinessGetByDetail
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(Security $security,EntityManagerInterface $manager)
    {

        $this->security = $security;
        $this->manager = $manager;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        if (!$this->security->isGranted('ROLE_SUPER_ADMIN')){
            throw new UnauthorizedHttpException('role','only super admin can create');
        }
        $qb=$this->manager->createQueryBuilder();
        $query=$qb->select('b')
            ->from(Business::class,'b')
            ->leftJoin('b.category','cat',Join::WITH)
            ->leftJoin('b.userBusinessRoles','ubr',Join::WITH)
            ->leftJoin('ubr.user','u')
            ->leftJoin('b.businessBanks','bank',Join::WITH)
            ->leftJoin('b.billing','billing',Join::WITH)
            ->addSelect('u')
            ->addSelect('ubr')
            ->addSelect('bank')
            ->addSelect('billing')
            ->addSelect('cat')
            ->where("ubr.role='account'")
            ->getQuery()
            ->getArrayResult();
           return $query;



    }

}
