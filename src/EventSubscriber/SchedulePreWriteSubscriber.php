<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Business;
use App\Entity\Schedule;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class SchedulePreWriteSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(BusinessFinder $businessFinder,EntityManagerInterface $manager)
    {
        $this->businessFinder = $businessFinder;
        $this->manager = $manager;
    }

    public function preWriteSchedule(ViewEvent $event)
    {
        $schedule = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$schedule instanceof Schedule || Request::METHOD_POST !== $method) {
            return;
        }

        if ($schedule->getBusinessId()===null){
            $business_id=$this->businessFinder->getUserBusiness();
            $business=$this->manager->getRepository(Business::class)->find($business_id);
            $schedule->setBusinessId($business);

        }

    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['preWriteSchedule',EventPriorities::PRE_WRITE],
        ];
    }
}
