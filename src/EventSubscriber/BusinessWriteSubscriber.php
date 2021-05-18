<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Billing;
use App\Entity\Business;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

class BusinessWriteSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var BusinessFinder
     */
    private $finder;

    public function __construct(EntityManagerInterface $manager,Timezone $timezone,Security $security,ParameterBagInterface $bag,BusinessFinder $finder)
    {
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->security = $security;
        $this->bag = $bag;
        $this->finder = $finder;
    }

    public function businessPreWrite(ViewEvent $event)
    {
        $business = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $path=$event->getRequest()->getRequestUri();
        if (!$business instanceof Business || Request::METHOD_POST !== $method  || $path !== '/api/businesses') {
            return;
        }


        //set business billing to default billings
        /**
         * @var Billing $defaultBilling
         */
        $billingRepo=$this->manager->getRepository(Billing::class);
        $defaultBilling=$billingRepo->findOneBy(['isDefault'=>true]);
        $timezone=$this->timezone->getDefaultTimeZone();
        $business->setBilling($defaultBilling);
        /**
         * @var DateTime $dateTime
         */

        try {
            $dateTime = new DateTime('now', new \DateTimeZone($timezone));
        } catch (\Exception $e) {
            dd($e);
        }
        try {
            $dateTime->add(new \DateInterval('P'.$defaultBilling->getPeriod() . 'D'));
        } catch (\Exception $e) {
            dd($e);
        }

        $time_string=$dateTime->format($this->timezone->getDefaultTimeFormat());


        $business->setExpireBilling($time_string);

    }

    public function businessPostWrite(ViewEvent $event)
    {
        $business = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $path=$event->getRequest()->getRequestUri();

        if ($business instanceof Business && Request::METHOD_POST == $method && $path=== '/api/businesses') {
            $userBusinessRole=new UserBusinessRole();
            $userBusinessRole->setUser($this->security->getUser());
            $userBusinessRole->setBusiness($business);
            $userBusinessRole->setRole(($this->bag->get('roles'))['account']);
            $this->manager->persist($userBusinessRole);
            $this->manager->flush();
            $this->finder->SetCurrentBusiness($business->getId());

        }


    }


    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => [
                    ['businessPreWrite',EventPriorities::PRE_WRITE],
                    ['businessPostWrite',EventPriorities::POST_WRITE]
            ],
        ];
    }
}
