<?php


namespace App\Controller\Business;



use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\InvalidValueException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Business;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SelectBusiness
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Security
     */
    private $security;
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(EntityManagerInterface $entityManager,CacheInterface $cache,Security $security)
    {

        $this->entityManager = $entityManager;

        $this->security = $security;
        $this->cache = $cache;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        $business_repo=$this->entityManager->getRepository(Business::class);

        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        /**
         * @var Business $business
         */
        $business=$business_repo->find($params['id_business']);

        if (!$business->getActive()){
            throw new InvalidValueException("this business is suspend now try later");
        }
        $userBusinessRole_repo=$this->entityManager->getRepository(UserBusinessRole::class);



        if($business!== null){
            $userBusinesses=$userBusinessRole_repo->findBy(["user"=>$user->getId(),'business'=>$business]);
           if (count($userBusinesses)>0){


               $cacheKey=$user->getUserCacheKeyBusiness();
               $cachedItem=$this->cache->getItem($cacheKey);
               /**
                * @var CacheItem $cachedItem
                */
               $cachedItem->set($params['id_business']);
               $this->cache->save($cachedItem);

           }else{
               throw new InvalidArgumentException('user not exist in the business');
           }


        }else{
            throw new ItemNotFoundException('business not found');
        }
        return $business;
    }


}
