<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AttendancePeriod;
use App\Entity\AttendanceSetting;
use App\Entity\Business;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AttendanceSettingSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(BusinessFinder $finder,EntityManagerInterface $manager)
    {
        $this->finder = $finder;
        $this->manager = $manager;
    }

    public function onViewEvent(ViewEvent $event)
    {
        /**
         * @var AttendanceSetting $attendanceSetting
         */
        $attendanceSetting=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$attendanceSetting instanceof AttendanceSetting || !( $method === Request::METHOD_POST || $method === Request::METHOD_PUT)){
            return;
        }
        /**
         * @var Business $business
         */
        $business=$this->finder->getCurrentUserBusiness();
        if ($method ===Request::METHOD_POST){

            $attendanceSetting->setBusiness($business);
            //if another business setting for business
            $attendance_setting_old=$this->manager->getRepository(AttendanceSetting::class)->findBy(['business'=>$business]);
            foreach ($attendance_setting_old as $set){
                $this->manager->remove($set);
            }

            //create periods per buisiness expire from entry time
            $expire=$business->getExpireBilling();
            try{
                $start=new \DateTime();
                $end=new \DateTime($expire);
                $this->generatePeriods($start,$end,$attendanceSetting->getPayrollLengthDefault());

            }catch (\Exception $e){
                throw new \HttpException("error generate period: ".$e->getMessage(),400);
            }

            return $attendanceSetting;

        }

        if ($method===Request::METHOD_PUT){
            $uow = $this->manager->getUnitOfWork();
            $uow->computeChangeSets(); // do not compute changes if inside a listener
            $changeset = $uow->getEntityChangeSet($attendanceSetting);

            if (array_key_exists('payrollLengthDefault',$changeset)){
                $interval=$changeset['payrollLengthDefault'][1];

                //find last unclosed period and delete from this to next then generate next one
                //not id is increament and period generate by id so first id is first one
                /**
                 * @var AttendancePeriod $periods_last
                 */
               $periods_last= $this->manager->getRepository(AttendancePeriod::class)->findBy(['business'=>$business,'closed'=>false]);

               if (count($periods_last)>0){
                   $first_unclosed=$periods_last[0];
                   $start=$first_unclosed->getStartTime();
                   $end=$business->getExpireBilling();
                   foreach ($periods_last as $p){
                       $this->manager->remove($p);
                   }
                   try {
                       $start_time=new \DateTime($start);
                       $end_time=new \DateTime($end);
                       $this->generatePeriods($start_time,$end_time,$interval);
                   }catch (\Exception $e){
                       throw new HttpException("exd");
                   }

               }


            }

        }
    }

    /**
     * @param $begin
     * @param \DateTime $end
     * @param $interval
     * @throws \Exception
     */
    public function generatePeriods($begin,$end,$interval)
    {
        $interval_date = new \DateInterval('P'.$interval.'D');
        $daterange = new \DatePeriod($begin, $interval_date ,$end);

        foreach($daterange as $date){
           $date_period=new AttendancePeriod();
           $date_period->setStartTime($date->format('Y-m-d 00:00'));
           $date_period->setEndTime($date->modify("+".($interval-1)."day")->format('Y-m-d 23:59'));
           $date_period->setBusiness($this->finder->getCurrentUserBusiness());
           $this->manager->persist($date_period);

        }
    }


    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent',EventPriorities::PRE_WRITE]
        ];
    }
}
