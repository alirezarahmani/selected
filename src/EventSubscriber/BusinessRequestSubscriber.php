<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\BusinessRequest;

use App\Entity\EmployeeAlert;
use App\Entity\TimeOffTotal;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\Notifier;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Security;

class BusinessRequestSubscriber implements EventSubscriberInterface
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
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(Security $security,
                                Timezone $timezone,
                                EntityManagerInterface $manager,
                                BusinessFinder $finder,
                                ParameterBagInterface $bag ,Notifier $notifier)
    {
        $this->security = $security;
        $this->timezone = $timezone;
        $this->manager = $manager;
        $this->finder = $finder;
        $this->bag = $bag;
        $this->notifier = $notifier;
    }

    public function onViewEvent(ViewEvent $event)
    {
        $business_request=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (! $business_request instanceof BusinessRequest ){
            return;
        }

        if($method === Request::METHOD_POST ){
            $business_id=$business_request->getBusiness();
            $admin_business=$this->manager->getRepository(UserBusinessRole::class)->findBy(['business'=>$business_id,'role'=>$this->bag->get('roles')['manager']]);
            /**
             * @var User $user
             */
            $user=$this->security->getUser();
            $user_roles=$this->manager->getRepository(UserBusinessRole::class)->findOneBy(['user'=>$user,'business'=>$business_id]);
            $user_request_prev=$this->manager->getRepository(BusinessRequest::class)->findOneBy(['userId'=>$user,'business'=>$business_id,'status'=>BusinessRequest::BUSINESS_REQUEST_SUSPEND]);

            if(!empty($user_roles) ){
                throw new InvalidArgumentException('user already exists in this business');
            }
            if ( !empty($user_request_prev)){
                throw new InvalidArgumentException('user already has requested to join in this business');
            }
            $business_request->setUserId($user);
            $business_request->setStatus(BusinessRequest::BUSINESS_REQUEST_SUSPEND);
            $business_request->setCreatedAt($this->timezone->generateSystemDate());
            $business_request->setUpdatedAt($this->timezone->generateSystemDate());

        }
        if ($method === Request::METHOD_PUT){
            $array=[BusinessRequest::BUSINESS_REQUEST_SUSPEND,BusinessRequest::BUSINESS_REQUEST_ACCEPTED,BusinessRequest::BUSINESS_REQUEST_DENIED];

            if (!in_array($business_request->getStatus(),$array)){
               throw new InvalidArgumentException('status not exists');
            }
            //add a role for requester user in business on accept requests
            if ($business_request->getStatus() === BusinessRequest::BUSINESS_REQUEST_ACCEPTED){
                $business=$this->finder->getCurrentUserBusiness();
                $number_of_user=$business->getUserBusinessRoles()->getKeys();
                $allowed_user_count=(int)($business->getBilling()->getNumberOfEmployee())+(int)$business->getAdditionalUsersCount();
                if ($allowed_user_count === count($number_of_user)){
                    throw new InvalidArgumentException("for more user upgrade your plan");
                }
                $business_role=new UserBusinessRole();
                $business_role->setBusiness($business);
                $business_role->setUser($business_request->getUserId());
                $business_role->setRole($this->bag->get('roles')['employee']);

                //create user alerts preference

                $employee_alert=new EmployeeAlert();
                $employee_alert->setUserId($business_request->getUserId());
                $this->manager->persist($employee_alert);

                //also whenever user join business a totalTimeOff row add in a database and
                // update when ever a time off of type holiday or sick accept or canceled
                $totalTimeOff=new TimeOffTotal();
                $totalTimeOff->setBusinessId($this->finder->getCurrentUserBusiness());
                $totalTimeOff->setUser($business_request->getUserId());//because by default user is zero contact so
                //totalDeservedHoliday is zero

                $this->manager->persist($totalTimeOff);
                $this->manager->persist($business_role);
                $this->manager->flush();

            }
        }



        return $business_request;
    }

    public function sendNotification(ViewEvent $event)
    {
        $business_request=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (! $business_request instanceof BusinessRequest ){
            return;
        }
        $business_id=$business_request->getBusiness();
        if($method === Request::METHOD_POST ) {
            $this->notifier->sendAccountManagerNotification($business_request, Notifier::NEW_EMPLOYEE, $business_id);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class =>[ ['onViewEvent',EventPriorities::PRE_WRITE],['sendNotification',EventPriorities::POST_WRITE]],
        ];
    }
}
