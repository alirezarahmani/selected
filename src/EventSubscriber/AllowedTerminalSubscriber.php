<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AllowedTerminalIp;
use App\Entity\Annotations;
use App\Entity\User;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

class AllowedTerminalSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var Security
     */
    private $security;

    public function __construct(BusinessFinder $finder,Timezone $timezone,Security $security)
    {
        $this->finder = $finder;
        $this->timezone = $timezone;
        $this->security = $security;
    }

    public function preWriteAnnotation(ViewEvent $event)
    {
        /**
         * @var AllowedTerminalIp $allowedTerminalIp
         */
        $allowedTerminalIp=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$allowedTerminalIp instanceof AllowedTerminalIp || $method !== Request::METHOD_POST){
            return ;

        }
        /**
         * @var Annotations $annotation
         * @var User $user
         */

        $allowedTerminalIp->setBusiness($this->finder->getCurrentUserBusiness());

        return $allowedTerminalIp;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['preWriteAnnotation',EventPriorities::PRE_WRITE]
        ];
    }
}
