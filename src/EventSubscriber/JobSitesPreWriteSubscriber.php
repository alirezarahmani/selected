<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\JobSites;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class JobSitesPreWriteSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(BusinessFinder $businessFinder, EntityManagerInterface $manager)
    {
        $this->businessFinder = $businessFinder;
        $this->manager = $manager;
    }

    public function jobSitesPreWrite(ViewEvent $event)
    {
        $jobSite = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$jobSite instanceof JobSites || Request::METHOD_POST !== $method) {
            return;
        }
        $business_id=$this->businessFinder->getUserBusiness();
        $business=$this->manager->getRepository(Business::class)->find($business_id);

        /**
         * @var JobSites $jobSite
         */
        if ($jobSite->getBusinessId()===null){
            $jobSite->setBusinessId($business);
        }

        //check jobsite schedule validation
        $jobSitesSchedule=$jobSite->getSchedules();
        foreach ($jobSitesSchedule as $schedule){
            if (!$business->getSchedules()->contains($schedule)){
                throw new InvalidArgumentException('schedule not exists in business');
            }
        }

    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['jobSitesPreWrite',EventPriorities::PRE_WRITE],
        ];
    }
}
