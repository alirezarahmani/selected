<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Availability;
use App\Service\BusinessFinder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class BusinessSettingImplementationSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $finder;

    public function __construct(BusinessFinder $finder)
    {
        $this->finder = $finder;
    }

    public function onViewEvent(ViewEvent $event)
    {

        $entity=$event->getRequest()->attributes->get('_api_resource_class');
        if(!isset($entity))
            return;
        $valid=true;
        $ent='';
        switch ($entity){
            case ($entity === Availability::class):
                $business=$this->finder->getCurrentUserBusiness();
                if (!$business->getAvailability()){
                    $valid=false;
                    $ent=Availability::class;
                }
                break;

            default:
                return;

        }
        if (!$valid)
         throw new InvalidArgumentException(sprintf('action on %s cannot performed base on business setting',$ent));

    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent',EventPriorities::PRE_VALIDATE]
        ];
    }
}
