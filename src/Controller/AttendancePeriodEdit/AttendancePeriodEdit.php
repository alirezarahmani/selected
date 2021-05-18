<?php


namespace App\Controller\AttendancePeriodEdit;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\AttendancePeriod;
use App\Entity\AttendanceTimes;
use App\Entity\AttendanceTimesLog;
use App\Entity\PeriodStaffResult;
use App\Entity\Shift;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\AttendanceSettingService;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function Doctrine\ORM\QueryBuilder;

class AttendancePeriodEdit
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var AttendanceSettingService
     */
    private $settingService;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(EntityManagerInterface $manager,RequestStack $requestStack,BusinessFinder $finder, AttendanceSettingService $settingService,Timezone $timezone)
    {
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->finder = $finder;
        $this->settingService = $settingService;
        $this->timezone = $timezone;
    }

    public function __invoke($data)
    {
        $request=$this->requestStack->getCurrentRequest();
        if ($data instanceof AttendancePeriod){
            $attendance_period=$data;
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
                dd($e->getMessage());
            }
            if (count($attendance_periods) > 0) {
                /**
                 * @var AttendancePeriod $last
                 */
                $last = $attendance_periods[0];
            }

            if (!is_null($last)) {
                //find shift in this periiod and disable editing shift and add one row for each of shift in payroll
                $uow = $this->manager->getUnitOfWork();
                $uow->computeChangeSet($this->manager->getClassMetadata(AttendancePeriod::class), $attendance_period);
                $changeSet = $uow->getEntityChangeSet($attendance_period);

                if (array_key_exists('startTime', $changeSet) || (array_key_exists('endTime', $changeSet) && $attendance_period->getId() !== $last->getId())) {
                    throw new InvalidArgumentException('you can only edit for time im last added period ,its end date');
                }

                if(array_key_exists('closed',$changeSet) && $changeSet['closed'][0]){
                    throw new HttpException(400,'user not permit to change closed period status');
                }
                if ($attendance_period->getClosed() && array_key_exists('closed',$changeSet)){
                    $start_time = $attendance_period->getStartTime();
                    $end_time = $attendance_period->getEndTime();
                    $this->setAttendanceForShift($start_time, $end_time);
                    //find all attendance
                    $query_builder = $this->manager->createQueryBuilder();
                    $query_builder->select('at')
                        ->from(AttendanceTimes::class, 'at')
                        ->leftJoin('at.shift', 's')
                        ->where($query_builder->expr()->andX(
                            $query_builder->expr()->gte('at.startTime', "'" . $start_time . "'"),
                            $query_builder->expr()->lt('at.startTime', "'" . $end_time . "'")));


                    $attendance_times = $query_builder->getQuery()->execute();

                    //check all attendance has clock out time
                    /** @var AttendanceTimes $at */
                    foreach ($attendance_times as $at) {
                        $end_at = $at->getEndTime();
                        $start_at = $at->getStartTime();
                        if ((empty($end_at) || !isset($end_at)) && ((isset($start_at)) || !empty($start_at))) {
                            $this->manager->refresh($attendance_period);
                            throw new HttpException(400, 'there is attendance without clockout');

                        }
                    }

                    $arr=[];//grouping attendance
                    if(count($attendance_times) > 0){
                        /** @var AttendanceTimes $at */
                        foreach ($attendance_times as $at){
                            $arr[$at->getUser()->getId()][]=$at;//grouping attendances
                        }

                    }

                    //calculate attendance for each user
                    foreach ($arr as $user_id => $user_attendance_arr) {

                        $user = $this->manager->getRepository(User::class)->find($user_id);
                        $qb = $this->manager->createQueryBuilder();
                        $qb->select('tor')
                            ->from(TimeOffRequest::class, 'tor')
                            ->where($qb->expr()->between('tor.startTime', "'" . $start_time . "'", "'" . $end_time . "'"))
                            ->orWhere($qb->expr()->between('tor.endTime', "'" . $start_time . "'", "'" . $end_time . "'"))
                            ->andWhere($qb->expr()->eq('tor.Status', "'" . TimeOffRequest::TIME_OFF_ACCEPT . "'"));

                        $time_offs = $qb->getQuery()->getResult();//this is all system time off off this business in this range date
                        $holiday_minutes = $this->getTotalHoliday($time_offs, $user_id, $start_time, $end_time);
                        $sick_minutes = $this->getSicks($time_offs, $user_id, $start_time, $end_time);
                        $totalMinutes = $this->getTotals($user_attendance_arr);
                        $total_auto_deduct=$this->calculateAutoDeduct($attendance_times);
                        $total_scheduled_minutes=$this->calculateScheduled($start_time,$end_time,$user_id);

                        $total_overtime_minutes=$totalMinutes-($total_scheduled_minutes+$total_auto_deduct);
                        $labor = $this->getLabors($user_id, $totalMinutes, $total_overtime_minutes,$total_scheduled_minutes, $sick_minutes, $holiday_minutes);

                        $staffResult = new PeriodStaffResult();
                        $staffResult->setUser($user);
                        $staffResult->setAttendancePeriod($attendance_period);
                        $staffResult->setHoliday($holiday_minutes);
                        $staffResult->setSick($sick_minutes);
                        $staffResult->setTotal($totalMinutes);
                        $staffResult->setTotalScheduled($total_scheduled_minutes);
                        $staffResult->setOt($total_overtime_minutes);
                        $staffResult->setAutoDeducted($total_auto_deduct);
                        $staffResult->setLabor($labor);
                        $staffResult->setCreatedAt($this->timezone->generateSystemDate());
                        $this->manager->persist($staffResult);
                    }



                }


            }
        }
        $this->manager->persist($attendance_period);
        return $attendance_period;


    }

    public function setAttendanceForShift($start_time, $end_time)
    {
        try {
            $query_builder_shift = $this->manager->createQueryBuilder();
            $query_builder_at = $this->manager->createQueryBuilder();

            $ids = [];
            $query_builder_at->select('IDENTITY(at.shift)')
                ->from(AttendanceTimes::class, 'at')
                ->where("at.startTime > '" . $start_time . "'")
                ->orWhere("at.endTime < '" . $end_time . "'");
            $shift_ids = $query_builder_at->getQuery()->execute();
            foreach ($shift_ids as $id) {
                $ids[] = $id[1];
            }

            //insert one row for each of shifts user not clockIN
            $query_builder_shift
                ->select('sh')
                ->from(Shift::class, 'sh')
                ->where($query_builder_shift->expr()->isNotNull('sh.ownerId'))
                ->andWhere($query_builder_shift->expr()->andX("sh.startTime > '" . $start_time . "'","sh.endTime < '" . $end_time . "'"));
            if (count($ids) > 0) {
                $query_builder_shift->andWhere($query_builder_shift->expr()->notIn('sh.id', $ids));
            }
            $shifts = $query_builder_shift->getQuery()->execute();

            /**
             * @var Shift $shift
             */
            foreach ($shifts as $shift) {
                $not_present_at = new AttendanceTimesLog();
                $not_present_at->setUser($shift->getOwnerId());
                $not_present_at->setType(AttendanceTimesLog::TYPES[2]);
                $not_present_at->setTime($shift->getStartTime());
                $not_present_at->setText('user absen on this day');
                $this->manager->persist($not_present_at);
            }
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

    }

    public function getSicks($timeOffs, $user_id, $start_time, $end_time)
    {
        $sick = 0;

        $timeOffs_sick = array_filter($timeOffs, function ($timeOff, $k) use ($user_id) {
            /**
             * @var TimeOffRequest $timeOff
             */
            return $timeOff->getUserId()->getId() === $user_id && $timeOff->getType() === TimeOffRequest::TIME_OFF_TYPE[1];

        }, ARRAY_FILTER_USE_BOTH);

        /**
         * @var TimeOffRequest $timeOff
         */
        foreach ($timeOffs_sick as $timeOff) {
            $sick_minutes = $this->calculate_diff_date($timeOff->getStartTime(), $timeOff->getEndTime(), $start_time, $end_time);
            $sick += $sick_minutes;
        }
        return $sick;
    }

    public function getTotalHoliday($timeOffs, $user_id, $start_time, $end_time)
    {
        $holiday = 0;

        $timeOffs_holiday = array_filter($timeOffs, function ($timeOff, $k) use ($user_id) {
            /**
             * @var TimeOffRequest $timeOff
             */
            return $timeOff->getUserId()->getId() === $user_id && $timeOff->getType() === TimeOffRequest::TIME_OFF_TYPE[2];

        }, ARRAY_FILTER_USE_BOTH);

        /**
         * @var TimeOffRequest $timeOff
         */
        foreach ($timeOffs_holiday as $timeOff) {
            $min_holiday = $this->calculate_diff_date($timeOff->getStartTime(), $timeOff->getEndTime(), $start_time, $end_time);
            $holiday += $min_holiday;
        }
        return $holiday;
    }

    public function getTotals($user_times)
    {
        $total = 0;
        /**
         * @var AttendanceTimes $attendanceTime
         */
        foreach ($user_times as $attendanceTime) {

            $total += $attendanceTime->getWorked();
        }
        return $total;
    }

    public function calculateAutoDeduct($attendance_times)
    {
        $auto_deduct_min=0;
        $at_grp_shft=[];
        $is_autoDeduct_mode=$this->settingService->getAttendanceSetting()->getAutomateCalculateBreak();
        if ($is_autoDeduct_mode){
            //group attendance by shift auto deducts calculate base on attendance
            /**
             * @var AttendanceTimes $t
             */
            foreach ($attendance_times as $t){
                if (!is_null($t->getShift())){dd($t->getStartTime());
                    $at_grp_shft['shift_'.$t->getShift()->getId()][]=$t;
                }else{
                    //id never is 0 ,push free shift
                    $at_grp_shft['shift_0'][]=$t;
                }

            }
            foreach ($at_grp_shft as $shift_id=>$att_array){
                $i=explode("_",$shift_id);//explode by _
                if ($i[1]!=='0'){//0 is not a shift id
                    /**
                     * @var Shift $shift
                     */
                    $shift=$this->manager->getRepository(Shift::class)->find($i[1]);
                    /**
                     * @var Shift $shift
                     */
                    $shift=$this->manager->getRepository(Shift::class)->find($i[1]);
                    if ($shift->getUnpaidBreak()>0){
                        $resolve=false;
                        /**
                         * @var AttendanceTimes $attendance_time
                         */
                        foreach ($attendance_times as $attendance_time){
                            if ($attendance_time->getBreak() > 0){//if breakstart be filled this means employee forgot to register break out
                                $resolve=true;
                            }
                        }
                        if (!$resolve){
                            $auto_deduct_min+=$shift->getUnpaidBreak();
                        }
                    }

                }


            }
           return $auto_deduct_min;

        }

    }

    public function calculateScheduled($start_time,$end_time,$user_id)
    {
        $total_scheduled=0;
        $shift_query_builder = $this->manager->createQueryBuilder();
        $shift_query_builder->select('sh')
            ->from(Shift::class, 'sh')
            ->where(
                $shift_query_builder->expr()->andX("sh.startTime > '" . $start_time . "'",
                                                     "sh.endTime < '" . $end_time . "'"))
            ->andWhere($shift_query_builder->expr()->eq('sh.ownerId',$user_id));

        $shifts=$shift_query_builder->getQuery()->execute();
        /**
         * @var Shift $shift
         */
        foreach ($shifts as $shift){
            $total_scheduled+=$shift->getScheduled();
        }
        return $total_scheduled;

    }

    public function getLabors($user_id, $totalMinutes, $total_overtime_minutes,$total_scheduled_minutes, $sick_minutes, $holiday_minutes)
    {
        $labor = 0;
        $user = $this->manager->getRepository(User::class)->find($user_id);
        /** @var UserBusinessRole $UBR */
        $UBR = $user->getUserBusinessRoles()->getValues()[0];
        $payroll = $UBR->getBaseHourlyRate();
        $payroll_ot = $UBR->getPayrollOT();

        $over_time = $UBR->getCalculateOT();
        $sick_paid = ($sick_minutes / 60) * (int)$payroll;
        $holiday_paid = ($holiday_minutes / 60) * (int)$payroll;

        if ($over_time) {//if overtime setting being true
            $paid = (($totalMinutes-$total_overtime_minutes) / 60) * (int)$payroll;
            $paid_ot = ($total_overtime_minutes / 60) * (int)$payroll_ot;
            $labor = $sick_paid + $holiday_paid + $paid + $paid_ot;
        } else {
            $paid = (($totalMinutes) / 60) * (int)$payroll;
            $labor = $sick_paid + $holiday_paid + $paid;

        }
        return $labor;

    }


    /**
     * @param $start
     * @param $end
     * @param $start_period
     * @param $end_period
     * @return float|int number of seconds between two date
     */
    public function calculate_diff_date($start, $end, $start_period, $end_period)
    {
        $end_pr = strtotime($end_period);
        $start_pr = strtotime($start_period);

        $date1 = strtotime($start) <= $start_pr ? $start_pr : strtotime($start);
        $date2 = strtotime($end) >= $end_pr ? $end_pr : strtotime($end);

        $diff_seconds = abs($date2 - $date1);
        return floor($diff_seconds / 60);
    }


}
