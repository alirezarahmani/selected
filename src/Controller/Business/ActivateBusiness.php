<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;

class ActivateBusiness
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(EntityManagerInterface $manager,
                                CacheInterface $cache,
                                Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->cache = $cache;
    }

    /**
     * @param Business $data
     */
    public function __invoke($data)
    {
        if(!$this->security->isGranted('ROLE_SUPER_ADMIN')){
            throw new InvalidValueException("only super admin can deactivate a business");
        }

        $this->manager->persist($data);
        $this->manager->flush();
        return $data;
    }

}
