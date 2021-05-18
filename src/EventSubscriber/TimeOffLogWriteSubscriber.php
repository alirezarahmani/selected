<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\TimeoffLog;
use App\Entity\User;
use App\Service\Notifier;
use App\Service\Timezone;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class TimeOffLogWriteSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(Security $security,Timezone $timezone,Notifier $notifier)
    {
        $this->security = $security;
        $this->timezone = $timezone;
        $this->notifier = $notifier;
    }

    public function onViewEvent(ViewEvent $event)
    {
        $timeOffLog=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$timeOffLog instanceof TimeoffLog || $method !==Request::METHOD_POST){
            return;
        }
        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        $timeOffLog->setCreatorId($user);
        $timeOffLog->setDate($this->timezone->generateSystemDate());
        $timeOffLog->setStatus(TimeoffLog::NON_STATUS);
        return $timeOffLog;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent',EventPriorities::PRE_WRITE],
        ];
    }
}
