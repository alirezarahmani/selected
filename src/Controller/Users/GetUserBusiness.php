<?php


namespace App\Controller\Users;


use App\Entity\User;
use App\Entity\UserBusinessRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class GetUserBusiness
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(Security $security,EntityManagerInterface $manager,SerializerInterface $serializer)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->serializer = $serializer;
    }

    public function __invoke()
    {
        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        $bus_role=$user->getUserBusinessRoles()->toArray();
        $result=array();

        foreach($bus_role as &$item){
            $result[]=$this->serializer->normalize($item,null,['groups'=>'user_business_read']);

        };
        return new JsonResponse($result,200);

    }

}