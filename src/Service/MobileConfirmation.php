<?php


namespace App\Service;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\User;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;

class MobileConfirmation
{
    /**
     * @var CacheInterface
     */
    private $cache;


    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getCode(User $user)
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        $cacheKey=$this->getUserVerificationKey($user);
        /**
         * @var CacheItem $verification_code
         */
        $verification_code=$this->cache->getItem($cacheKey);
        if (false === $verification_code->isHit()){
            throw new InvalidArgumentException('no verification code set, request again');
        }
       return $verification_code->get();


    }

    /**
     * @param User $user
     * @return int
     */
    public function saveCode($user)
    {
        $cacheKey=$this->getUserVerificationKey($user);
        $code='1234';
        $cachedItem=$this->cache->getItem($cacheKey);
        /**
         * @var CacheItem $cachedItem
         */
        $cachedItem->set($code);
        $cachedItem->expiresAfter(60*15); // 900 seconds = 15 minute
        $this->cache->save($cachedItem);
        return $code;

    }

    public function issetVerification($user)
    {
        $cacheKey=$this->getUserVerificationKey($user);
        /**
         * @var CacheItem $verification_code
         */
        $verification_code=$this->cache->getItem($cacheKey);
        return $verification_code->isHit();
    }

    private function getUserVerificationKey($user)
    {
        return 'userverification'.$user->getId();
    }
}
