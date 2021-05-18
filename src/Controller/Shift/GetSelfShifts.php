<?php


namespace App\Controller\Shift;


use App\Entity\Shift;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class GetSelfShifts
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $manager,Security  $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    public function __invoke()
    {
        $user=$this->security->getUser();
        $shift_repo=$this->manager->getRepository(Shift::class);
        $shifts=$shift_repo->findByUsers($user);

       return $shifts;

    }

}
