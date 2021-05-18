<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\Schedule;
use App\Entity\ShiftTemplate;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class ShiftTemplateSubscriber implements EventSubscriberInterface
{
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $businessFinder;

    public function __construct(Timezone $timezone,EntityManagerInterface $manager,BusinessFinder $businessFinder)
    {
        $this->timezone = $timezone;
        $this->manager = $manager;
        $this->businessFinder = $businessFinder;
    }

    public function shiftTemplatePreWrite(ViewEvent $event)
    {
        $shift_template = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$shift_template instanceof ShiftTemplate || Request::METHOD_POST !== $method) {
            return;
        }
        $shift_template->setStartTime($this->timezone->transformUserDateToAppTimezone($shift_template->getStartTime(),'H:i'));
        $shift_template->setEndTime($this->timezone->transformUserDateToAppTimezone($shift_template->getEndTime(),'H:i'));

        //validate properties  true initialized
        $business =$this->businessFinder->getCurrentUserBusiness();
        $schedules =$shift_template->getScheduleId();


        if (!empty($schedules) && !$business->getSchedules()->contains($schedules))
            throw new InvalidArgumentException('business not contain schedule');
        $positions =$shift_template->getPositionId();
        if (!empty($positions) && !$business->getPositions()->contains($positions))
            throw new InvalidArgumentException('business not contain positions');

    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['shiftTemplatePreWrite',EventPriorities::PRE_WRITE],
        ];
    }
}
