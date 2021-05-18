<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\ShiftRequestLog;
use App\Entity\User;
use App\Service\Timezone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

class ShiftRequestLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(Security $security,Timezone $timezone)
    {
        $this->security = $security;
        $this->timezone = $timezone;
    }

    public function prePost(ViewEvent $event)
    {
        $shiftRequestLog=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$shiftRequestLog instanceof ShiftRequestLog || !Request::METHOD_POST === $method || !empty($shiftRequestLog->getType()))
            return ;
        $shiftRequestLog->setType(ShiftRequestLog::NON_TYPE);
        /**
         * @var User $user
         */
        $user =$this->security->getUser();
        $shiftRequestLog->setCreatorId($user);
        $shiftRequestLog->setType(ShiftRequestLog::NON_TYPE);
        $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());

        return $shiftRequestLog;

    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['prePost',EventPriorities::PRE_WRITE],
        ];
    }
}
