<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Business;
use App\Entity\Position;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class PositionPreWriteSubscriber implements EventSubscriberInterface
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

    public function positionPreWrite(ViewEvent $event)
    {
        $position = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$position instanceof Position || Request::METHOD_POST !== $method) {
            return;
        }
        $business_id=$this->businessFinder->getUserBusiness();
        $business=$this->manager->getRepository(Business::class)->find($business_id);


        if ($position->getBusinessId()===null){
            $position->setBusinessId($business);

        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['positionPreWrite',EventPriorities::PRE_WRITE],
        ];
    }
}
