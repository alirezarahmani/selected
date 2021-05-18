<?php


namespace App\Controller\BusinessRequest;


use App\Entity\BusinessRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class GetUserRequest
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

    public function __invoke()
    {

        /**
         * @var User $current_user
         */
        $current_user=$this->security->getUser();
        $requests=$this->manager->getRepository(BusinessRequest::class)->findBy(['userId'=>$current_user,'status'=>BusinessRequest::BUSINESS_REQUEST_SUSPEND]);
        return $requests;


    }


}
