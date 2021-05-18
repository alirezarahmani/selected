<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Annotations;
use App\Entity\User;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

class AnnotationWriteSubscriber implements EventSubscriberInterface
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
        $annotation=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$annotation instanceof Annotations || $method !== Request::METHOD_POST){
            return ;

        }
        /**
         * @var Annotations $annotation
         * @var User $user
         */
        $user=$this->security->getUser();
        $annotation->setBusinessId($this->finder->getCurrentUserBusiness());
        $annotation->setCreatedBy($user);
        $annotation->setCreatedAt($this->timezone->generateSystemDate());
        $annotation->setStartDate($this->timezone->transformUserDateToAppTimezone($annotation->getStartDate()));
        $annotation->setEndDate($this->timezone->transformUserDateToAppTimezone($annotation->getEndDate()));

        return $annotation;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['preWriteAnnotation',EventPriorities::PRE_WRITE]
        ];
    }
}
