<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\TimeoffLog;
use App\Entity\TimeOffRequest;
use App\Entity\TimeOffTotal;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\Notifier;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;
use function Doctrine\ORM\QueryBuilder;

class TimeOffSubscriber implements EventSubscriberInterface
{
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(Timezone $timezone,
                                Security $security,
                                EntityManagerInterface $manager,
                                Notifier $notifier,
                                BusinessFinder $finder)
    {
        $this->timezone = $timezone;
        $this->security = $security;
        $this->manager = $manager;
        $this->finder = $finder;
        $this->notifier = $notifier;
    }


    public function timeOffRequestBeforeWrite(ViewEvent $event)
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        $timeOff = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if (!$timeOff instanceof TimeOffRequest){
            return;
        }
        if ($timeOff instanceof TimeOffRequest && (Request::METHOD_POST === $method || Request::METHOD_PUT === $method)) {
           if ($method === Request::METHOD_POST){
               if (!in_array($timeOff->getType(),TimeOffRequest::TIME_OFF_TYPE) ){
                   throw new InvalidArgumentException('not exists type in time off type');
               }

               $timeOffLog=new TimeoffLog();
               $timeOff->setStartTime($this->timezone->transformUserDateToAppTimezone($timeOff->getStartTime()));
               $timeOff->setEndTime($this->timezone->transformUserDateToAppTimezone($timeOff->getEndTime()));
               $timeOff->setCreatedAt($this->timezone->generateSystemDate());
               $timeOff->setUserCreatorId($user);


               if ($this->security->isGranted('BUSINESS_SUPERVISOR')) {

                   $timeOff->setStatus(TimeOffRequest::TIME_OFF_ACCEPT);
                   $timeOffLog->setStatus(TimeOffRequest::TIME_OFF_ACCEPT);
                   $this->addToTotalTimeOff($timeOff);
                   $this->notifier->sendNotification($timeOff->getUserId(),
                                                    Notifier::createMessage('time off request',$timeOff->getUserId().' new timeoff request for you for detail see admin panel'),
                                                    $timeOff
                   );

               }elseif (!$this->security->isGranted('BUSINESS_SUPERVISOR') && $timeOff->getUserId()!== $user){
                   throw new HttpException(400,"your not permit to register timeoff for other user");

               }elseif ($this->finder->getCurrentUserBusiness()->getApproveTimeoffEmp()){
                   $timeOff->setStatus(TimeOffRequest::TIME_OFF_ACCEPT);
                   $timeOffLog->setStatus(TimeOffRequest::TIME_OFF_ACCEPT);
                   $this->addToTotalTimeOff($timeOff);
                   $this->notifier->sendNotification($timeOff->getUserId(),
                       Notifier::createMessage('time off request',$timeOff->getUserId().' new timeoff request for you for detail see admin panel'),
                       $timeOff
                   );
               } else {
                   $timeOff->setStatus(TimeOffRequest::TIME_OFF_CREATED);
                   $timeOffLog->setStatus(TimeOffRequest::TIME_OFF_CREATED);
                   $this->notifier->sendNotification($timeOff->getUserId(),
                       Notifier::createMessage('time off request',$timeOff->getUserId().' new timeoff request for you for detail see your panel'),
                       $timeOff
                   );
                   //find system managers
                   /**
                    * @var Business $business
                    */
                   $business=$this->finder->getCurrentUserBusiness();
//                 $users=$business->getUserBusinessRoles();
//                   foreach ($users as $usb){
//                       if (in_array($usb->getRole(),['manager','supervisor'])){
//                           //send notification
//                           $this->notifier->sendNotification($usb->getUser(),Notifier::createMessage('time off request',$timeOff->getUserId().' ask time off requests'));
//
//                       }
//                   }

               }
               $timeOffLog->setMessage($timeOff->getMessage());
               $timeOff->setBusinessId($this->finder->getCurrentUserBusiness());
               $timeOffLog->setDate($this->timezone->generateSystemDate());
               $timeOffLog->setCreatorId($user);
               $timeOffLog->setTimeOffRequstId($timeOff);
               $this->manager->persist($timeOff);
               $this->manager->persist($timeOffLog);

           }

           if ($method == Request::METHOD_PUT){
               if (!in_array($timeOff->getStatus(),
                   [TimeOffRequest::TIME_OFF_ACCEPT,TimeOffRequest::TIME_OFF_DENIED,TimeOffRequest::TIME_OFF_CANCELED
               ])){
                   throw new InvalidArgumentException('status not found in valid status list');
               }
                //only supervisor or upper can accept or denied request
               if(in_array($timeOff->getStatus(),[TimeOffRequest::TIME_OFF_ACCEPT,TimeOffRequest::TIME_OFF_DENIED]) && !$this->security->isGranted('BUSINESS_SUPERVISOR')){
                   throw new InvalidArgumentException('user not permit to accept or denied timeOffRequest');
               }

               if ($timeOff->getStatus()=== TimeOffRequest::TIME_OFF_CANCELED){//cancel only happens in put so no need to add following to put
                  $this->deductTotalTimeOff($timeOff);
                   $this->notifier->sendAccountManagerNotification($timeOff,
                       Notifier::createMessage('time off request',$timeOff->getUserId().' new timeoff request for you for detail see admin panel'),
                       $timeOff
                   );
               }

               //only user and manager can edit requests

               if ($user->getId() == $timeOff->getUserId()->getId() ||
                   $this->security->isGranted('BUSINESS_SUPERVISOR')){
                   $timeOffLog=new TimeoffLog();
                   $timeOffLog->setDate($this->timezone->generateSystemDate());
                   $timeOffLog->setCreatorId($this->security->getUser());
                   $timeOffLog->setTimeOffRequstId($timeOff);
                   $timeOffLog->setStatus($timeOff->getStatus());
                   $timeOffLog->setMessage($timeOff->getMessage());
                   $this->manager->persist($timeOff);
                   $this->manager->persist($timeOffLog);
               }else{
                   return new UnauthorizedHttpException('role','you have not permitted to cancel');
                }

               if ($timeOff->getStatus()===TimeOffRequest::TIME_OFF_ACCEPT){
                   $this->addToTotalTimeOff($timeOff);

               }

           }


        }
    }

    /**
     * @param TimeOffRequest $timeOff
     */
    public function addToTotalTimeOff($timeOff)
    {
        /**
         * @var TimeOffTotal $timeOffTotal
         */
        $timeOffTotal=$this->manager->getRepository(TimeOffTotal::class)->findBy(['user'=>$timeOff->getUserId(),'businessId'=>$this->finder->getCurrentUserBusiness()]);

        $timeOffDurationPaid=$timeOff->getPaidHour()*60;
        if ($timeOff->getType()===TimeOffRequest::TIME_OFF_TYPE[2]){//HOLIDAY TOTAL
            $latsHoliday=$timeOffTotal[0]->getTotalHoliday();
            $total=$latsHoliday+$timeOffDurationPaid;
            $timeOffTotal[0]->setTotalHoliday($total);
            $this->manager->persist($timeOffTotal[0]);

        }
        if ($timeOff->getType()===TimeOffRequest::TIME_OFF_TYPE[1]){//SICK TOTAL
            $latsSick=$timeOffTotal[0]->getTotalSick();
            $total=$latsSick+$timeOffDurationPaid;
            $timeOffTotal[0]->setTotalHoliday($total);
            $this->manager->persist($timeOffTotal[0]);

        }
    }

    /**
     * @param TimeOffRequest $timeOff
     */
    public function deductTotalTimeOff($timeOff)
    {
        /**
         * @var TimeOffTotal $timeOffTotal
         */
        $timeOffTotal=$this->manager->getRepository(TimeOffTotal::class)->findBy(['user'=>$timeOff->getUserId(),'businessId'=>$this->finder->getCurrentUserBusiness()]);
        $timeOffDurationPaid=$timeOff->getPaidHour()*60;
        if ($timeOff->getType()===TimeOffRequest::TIME_OFF_TYPE[2]){//HOLIDAY TOTAL
            $latsHoliday=$timeOffTotal[0]->getTotalHoliday();
            $total=$latsHoliday-$timeOffDurationPaid;
            $timeOffTotal[0]->setTotalHoliday($total);
            $this->manager->persist($timeOffTotal[0]);

        }
        if ($timeOff->getType()===TimeOffRequest::TIME_OFF_TYPE[1]){//SICK TOTAL
            $latsSick=$timeOffTotal[0]->getTotalSick();
            $total=$latsSick-$timeOffDurationPaid;
            $timeOffTotal[0]->setTotalHoliday($total);
            $this->manager->persist($timeOffTotal[0]);

        }

    }

    public function timeOffValidate(ViewEvent $event)
    {
        $time_off=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if(!$time_off instanceof TimeOffRequest)
            return;

        //checdk maxdays timeoff of system
        $max_day_time_off=$this->finder->getCurrentUserBusiness()->getMaxDaysTimeOff();
        $max_hour_time_off=$this->finder->getCurrentUserBusiness()->getMaxHourTimeoffPerDay();
        if (!empty($max_day_time_off)){
            $month_start=date('Y-m-01');
            $month_end=date('Y-m-t');
            $time_off_start=$this->timezone->transformUserDateToAppTimezone($time_off->getStartTime());
            $time_off_end=$this->timezone->transformUserDateToAppTimezone($time_off->getEndTime());

            $query=$this->manager->createQueryBuilder();
            $query->select('t')
                ->from(TimeOffRequest::class,'t')
                ->where(
                    $query->expr()->gt('t.startTime',"'".$month_start."'"))
                ->andWhere(
                    $query->expr()->lt('t.startTime',"'".$month_end."'"));
            $time_offs=$query->getQuery()->getResult();
            $days=0;//one day is 1440 minutes
            /**
             * @var TimeOffRequest $time
             */
            foreach ($time_offs as $time){
                try {
                    $diff = date_diff(new \DateTime($time->getEndTime()), new \DateTime($time->getStartTime()));
                } catch (\Exception $e) {
                    dd($e->getMessage());
                }
               $days+=(int)($diff->format('%d'))*1440+(int)($diff->format('%h'))*60+(int)($diff->format('%i'));

            }
            $time_off_diff=date_diff(new \DateTime($time_off_end),new \DateTime($time_off_start));
            $days+=(int)($time_off_diff->format('%d'))*1440+(int)($time_off_diff->format('%h'))*60+(int)($time_off_diff->format('%i'));

            if(($days/1440) > (int)$max_day_time_off){
                throw new InvalidArgumentException('time off exceeds of allowed buisness timeoff days');
            };
        }

        if (($max_hour_time_off)!=24 && !$time_off->getAllDay()){//validate time off not be exceed max hour
            try {
                $diff = date_diff(new \DateTime($time_off->getEndTime()), new \DateTime($time_off->getStartTime()));
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
            $diff_min=(int)($diff->format('%d'))*1440+(int)($diff->format('%h'))*60+(int)($diff->format('%i'));
            if ($diff_min/60 > $max_hour_time_off)
                throw new InvalidArgumentException('time off exceed max hour time off in one day ');

        }
    }

    public function timeOffPostWrite(ViewEvent $event)
    {
        $user = $this->security->getUser();
        $timeOff = $event->getControllerResult();
        if (!$timeOff instanceof TimeOffRequest){
            return;
        }
        $this->notifier->sendAccountManagerNotification($timeOff,Notifier::TIME_OFF_REQUEST);

    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => [
                ['timeOffRequestBeforeWrite',EventPriorities::PRE_WRITE],
                ['timeOffValidate',EventPriorities::PRE_VALIDATE],
                ['timeOffPostWrite',EventPriorities::POST_WRITE]
                ]
        ];
    }
}
