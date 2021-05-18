<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\AttendancePeriod;
use App\Entity\AttendanceTimes;
use App\Entity\AttendanceTimesLog;
use App\Entity\Business;
use App\Entity\PeriodStaffResult;
use App\Entity\Shift;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\AttendanceSettingService;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Cassandra\Date;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\UnitOfWork;
use mysql_xdevapi\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\Time;
use function Doctrine\ORM\QueryBuilder;

class AttendancePeriodsWriteSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var AttendanceSettingService
     */
    private $settingService;

    public function __construct(BusinessFinder $finder,
                                EntityManagerInterface $manager,
                                Timezone $timezone,
                                AttendanceSettingService $settingService)
    {
        $this->finder = $finder;
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->settingService = $settingService;
    }

    public function onViewEvent(ViewEvent $event)
    {
        $attendance_period = $event->getControllerResult();
        $request = $event->getRequest();
        $method = $event->getRequest()->getMethod();
        if (!$attendance_period instanceof AttendancePeriod) {
            return;
        }
        if ( $method !== Request::METHOD_POST) {
            return;
        }


        //get last row of periods in db,only last row end time can be edited~~~~~~~~~~~~~~~~~~~~~~~~~~
        $last = null;

        $query = $this->manager->createQueryBuilder();
        //find first prev attendance period
        $query->select('ap')
            ->from(AttendancePeriod::class, 'ap')
            ->where($query->expr()->eq('ap.business', $this->finder->getUserBusiness()))
            ->orderBy('ap.id', 'DESC');
        try {
            $attendance_periods = $query->getQuery()->getResult();
        } catch (HttpException $e) {
            throw new HttpException($e->getMessage());
        }
        if (count($attendance_periods) > 0) {
            /**
             * @var AttendancePeriod $last
             */
            $last = $attendance_periods[0];
        }
        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        if ($attendance_period instanceof AttendancePeriod && $method === Request::METHOD_POST) {
            /**
             * @var Business $business
             */
            $business=$this->finder->getCurrentUserBusiness();
            $attendance_period->setBusiness($business);

            //validation period
            /*validations:1-no conflict 2-valid date */
            $start_time = $attendance_period->getStartTime();
            $end_time = $attendance_period->getEndTime();


            if (strtotime($start_time) > strtotime($end_time)) {
                throw new InvalidArgumentException('invalid period');
            }//end time should be after start


            if (!empty($last)) {

                $last_end = strtotime($last->getEndTime() . ' +1 minutes');

                $start = strtotime($attendance_period->getStartTime());


                if (!($start >= $last_end && $start === $last_end)) {
                    throw new InvalidArgumentException('your selected period has gap or has conflict ');
                }

            }//check conflict with last periods ,it works because we do not let edit except last one or delete except last one


        }//before post check


        if ($attendance_period instanceof AttendancePeriod && $method === Request::METHOD_DELETE) {
            if (!empty($last)) {

                if ($attendance_period->getId() !== $last->getId()) {
                    throw new InvalidArgumentException('only last one period can delete');
                }
            }
        }
    }


    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent', EventPriorities::PRE_WRITE],
        ];
    }


}
