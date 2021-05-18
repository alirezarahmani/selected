<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Billing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

class BillingEventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $manager,Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent', EventPriorities::PRE_WRITE],
        ];
    }

    public function onViewEvent(ViewEvent $event)
    {
        $biling = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$biling instanceof Billing || !(Request::METHOD_POST === $method || Request::METHOD_PUT === $method)) {
            return;
        }

        if (!$this->security->isGranted('ROLE_SUPER_ADMIN')){
            throw new InvalidArgumentException("only super admin can change or edit billings");
        }
        return $biling;

    }
}

