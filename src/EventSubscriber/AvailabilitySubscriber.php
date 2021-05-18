<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Availability;
use App\Entity\User;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;

class AvailabilitySubscriber implements EventSubscriberInterface
{
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(Timezone $timezone, BusinessFinder $finder, Security $security, EntityManagerInterface $manager)
    {
        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->security = $security;
        $this->manager = $manager;
    }

    public function onViewEvent(ViewEvent $event)
    {
        $availability = $event->getControllerResult();
        $request = $event->getRequest();
        $method = $event->getRequest()->getMethod();
        if (!$availability instanceof Availability) {
            return;
        }


        if ($method === Request::METHOD_DELETE) {
            /**
             * @var User $user
             */
            $user = $this->security->getUser();
            $this->preDelete($availability, $user, $request);

        } else {
            return;
        }


    }


    /**
     * @param Availability $availability
     * @param $user
     * @param Request $request
     * @throws \Exception
     */
    public function preDelete($availability, $user, $request)
    {
        $req_content = json_decode($request->getContent(), true);
        $chain = $req_content['chain'];
        if ($chain) {
            $sibling = $this->manager->getRepository(Availability::class)->findBy(['parentAvailable' => $availability->getParentAvailabilityId()]);
            foreach ($sibling as $item) {
                $now = strtotime($this->timezone->generateSystemDate());
                $start_date = strtotime($item->getStartTime());
                if ($start_date > $now) {
                    $this->manager->remove($item);
                }
            }
        }
        //delete parent
        $parent_availability=$availability->getParentAvailabilityId();
        if (isset($parent_availability) && $availability->getId() == $parent_availability->getId() && !$chain) {
            $siblings = $this->manager->getRepository(Availability::class)->findBy(['parentAvailable' => $availability->getParentAvailabilityId()]);
            /**
             * @var Availability $obj
             */
            foreach ($siblings as $obj) {
                $obj->setRepeated(false);
                $obj->setEndReapetedTime(null);
                $obj->setDays(null);
                $this->manager->persist($obj);
            }
        }


    }



public static function getSubscribedEvents()
{
    return [
        ViewEvent::class => ['onViewEvent', EventPriorities::PRE_WRITE]


    ];
}
}
