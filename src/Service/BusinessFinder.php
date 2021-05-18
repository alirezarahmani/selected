<?php


namespace App\Service;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;

class BusinessFinder
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(CacheInterface $cache, Security $security,EntityManagerInterface $manager)
    {
        $this->cache = $cache;
        $this->security = $security;
        $this->manager = $manager;
    }

    public function getUserBusiness()
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $cacheKey=$user->getUserCacheKeyBusiness();
        /**
         * @var CacheItem $business_cash
         */
        $business_cash=$this->cache->getItem($cacheKey);
        if (false === $business_cash->isHit()){
            throw new InvalidArgumentException('business should be selected not cached');
        }
        $business_id=$business_cash->get();
        $business=$this->manager->getRepository(Business::class)->findOneBy(["id"=>$business_id]);
        if ($business == null){
            throw new InvalidArgumentException('business is removed in voter');
        }
        return $business_id;

    }

    public function getCurrentUserBusiness()
    {
        $business_id=$this->getUserBusiness();
        $business=$this->manager->getRepository(Business::class)->find($business_id);
        return $business;


    }

    public function SetCurrentBusiness($business_id)
    {
        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        $cacheKey=$user->getUserCacheKeyBusiness();
        $cachedItem=$this->cache->getItem($cacheKey);
        /**
         * @var CacheItem $cachedItem
         */
        $cachedItem->set($business_id);
        $this->cache->save($cachedItem);

    }

}
