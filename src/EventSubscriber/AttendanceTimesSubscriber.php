<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\AllowedTerminalIp;
use App\Entity\AttendancePeriod;
use App\Entity\AttendanceSetting;
use App\Entity\AttendanceTimes;
use App\Entity\AttendanceTimesLog;
use App\Entity\Business;
use App\Entity\Shift;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\AttendanceService;
use App\Service\AttendanceSettingService;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use function Doctrine\ORM\QueryBuilder;

class AttendanceTimesSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var AttendanceSettingService
     */
    private $attendanceSettingService;
    /**
     * @var EntityManager
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var RequestStack
     */
    private $requestStack;

    private $request_content;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var AttendanceService
     */
    private $attendanceService;


    private $ip=null;//client ip

    private $is_mobile=false;


    public function __construct(Security $security,
                                AttendanceSettingService $attendanceSettingService,
                                EntityManagerInterface $manager,
                                BusinessFinder $finder,
                                Timezone $timezone,
                                SerializerInterface $serializer,
                                AttendanceService $attendanceService,
                                RequestStack $requestStack)
    {
        $this->security = $security;
        $this->attendanceSettingService = $attendanceSettingService;
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;

        $this->attendanceService = $attendanceService;
    }


    public function onViewEvent(ViewEvent $event)
    {

        $attendanceTime = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $path_info =$event->getRequest()->getPathInfo();
        /*pervent public terminal be here*/
        if ($path_info==="/api/login_terminal" || $path_info==="login_terminal_auth"){
            return;
        }
        if (!$attendanceTime instanceof AttendanceTimes) {
            return;
        }

        if (!($method===Request::METHOD_PUT || $method===Request::METHOD_POST)){
            return;
        }

        $this->ip=($event->getRequest()->getClientIp());

        /**
         * @var Business $business
         */
        $business=$this->finder->getCurrentUserBusiness();
        $this->request_content = json_decode($this->requestStack->getCurrentRequest()->getContent(), true);
        //find attendance setting to check ip restriction~~~~~~~~~~~~~~~~~~~~~~~~
        $attendance_setting=$this->attendanceSettingService->getAttendanceSetting();
        $valid_ip_list=$business->getAllowedTerminalIps()->getValues();
        $personal_computer_restriction=$attendance_setting->getRestrictIpforPersonalAttendance();
        $this->is_mobile=$this->isMobile();

        //if folowwing true means admin is inserting from clock in clouck out panel directly user attendance
        $check_insertion_fromDashboard=$this->security->isGranted('BUSINESS_SUPERVISOR') && array_key_exists('startTime', $this->request_content) && array_key_exists('endTime', $this->request_content);

        if ($personal_computer_restriction && !$this->is_mobile && !$check_insertion_fromDashboard){
            $in_range=false;
            /**
             * @var AllowedTerminalIp $allowed
             */
            foreach($valid_ip_list as $allowed){
                $valid=$this->ip===$allowed->getIp();
                if ($valid){
                    $in_range=true;
                    $attendanceTime->setClockInLocation($allowed->getLocation());

                    break;
                }
            }

            if (!$in_range){
                throw new InvalidArgumentException("from your location with this ip clock in is not possible");
            }
        }
        //find attendance setting to check ip restriction~~~~~~~~~~~~~~~~~~~~~`



        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        try {
            $val = (new \DateTimeImmutable($attendanceTime->getStartTime()))->format('Y-m-d H:i');
        } catch (\Exception $e) {
            throw new HttpException(400,$e->getMessage());
        }
        //check if attendance not be in the closed date

        $this->setTimeZonesAttendance($attendanceTime);
        $qb=$this->manager->getRepository(AttendancePeriod::class)->createQueryBuilder('ap');
        $qb->select('count(ap.id)')
            ->where($qb->expr()->andX($qb->expr()->gte("'".$val."'",'ap.startTime'),$qb->expr()->lte("'".$val."'",'ap.endTime')))
            ->andWhere($qb->expr()->eq('ap.closed',1))
            ->andWhere($qb->expr()->eq('ap.business',$business->getId()));
        $rs=$qb->getQuery()->getSingleScalarResult();
       if ((int)$rs>0){
           throw new HttpException(400,'change in closed date is not permitted');
       }


        if ($method === Request::METHOD_POST) {
            $this->prePost($attendanceTime, $user);
        }
        if ($method === Request::METHOD_PUT) {
            $this->prePut($attendanceTime, $user);
        }
        if ($method === Request::METHOD_DELETE){
            $this->preDelete($attendanceTime,$user);
        }

    }

    /**
     * @param AttendanceTimes $attendanceTime
     * @param User $user
     * @throws \Exception
     */
    public function prePost($attendanceTime, $user)
    {
        /**
         * @var AttendanceSetting $setting
         */
        if (is_null($attendanceTime->getStartTime())) {//user must clock in to be able to clock out
            throw new InvalidArgumentException('you not register clock in ask admin to register your time for today');
        }

        $setting = $this->attendanceSettingService->getAttendanceSetting();
        $early_time = $setting->getEarlyLoginAllowed();
        //set attendance requirement
        /**
         *@var Business $business
         */
        $business = $this->finder->getCurrentUserBusiness();
        $attendanceTime->setBusiness($business);
        //set startTime
        if (!$this->security->isGranted('BUSINESS_SUPERVISOR') && array_key_exists('startTime', $this->request_content)) {
            $start_time = $this->timezone->generateSystemDate();
            $attendanceTime->setStartTime($start_time);
        }
        //set User
        if (!array_key_exists('user', $this->request_content)) {
            $attendanceTime->setUser($user);
        } else {
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')) {
                throw new UnauthorizedHttpException('you cannot set other timesheets');
            }
        }
        //find shift user can clockin in one shift multiple

        //find_shift,in post only have start time
        if (!is_null($attendanceTime->getStartTime())){
        //two condition we have either force map or shift is null and find for it
        if (is_null($attendanceTime->getShift())){//if user not send shift we find proper shift
            $position = $attendanceTime->getPosition();
            $schedule = $attendanceTime->getSchedule();
            if (is_null($schedule)){
                throw new HttpException(400,"your schedule should not be null");
            }
            $start_time = $attendanceTime->getStartTime();
            $start_time_to_search = date($this->timezone->getDefaultTimeFormat(), strtotime($start_time) + ($early_time * 60));
            $shift_repo = $this->manager->getRepository(Shift::class);
            $queryBuilder = $shift_repo->createQueryBuilder('s');
            $queryBuilder->select('s')
                ->where($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gte("'" . $start_time_to_search . "'", 's.startTime'),
                    $queryBuilder->expr()->gt("'" . $start_time . "'", 's.startTime')
                ))
                ->andWhere($queryBuilder->expr()->lt("'" . $start_time . "'", 's.endTime'))
                ->andWhere('s.ownerId =' . $user->getId())
                ->andWhere($queryBuilder->expr()->eq('s.editable',true));

            $shift_array = $queryBuilder->getQuery()->execute();

            if (!count($shift_array) > 0) {
                $shift = null;
            } else {
                //we don't want let employee to clock in other shift if shift define for him
                //if shif defined we calculate how many till now worked in th shift
                $resolve_shift=false;
                /**
                 * @var Shift $shift
                 */
                foreach ($shift_array as $sh){
                    if (!is_null($sh->getScheduleId()) && $sh->getScheduleId()->getId() === $schedule->getId()){
                        if (!is_null($sh->getPositionId()) &&!is_null($position) && $sh->getPositionId()->getId() === $position->getId()){
                            $resolve_shift=true;
                            $shift=$sh;
                        }else{
                            if (is_null($position) && is_null($sh->getPositionId())){
                                $resolve_shift=true;
                                $shift=$sh;
                            }else{
                                throw new HttpException(400,"your position for clockin the shift should not be this selected");
                            }
                        }
                    }
                }

                if (!$resolve_shift){
                    throw new HttpException(400,"you should clock in in defined shift in your schedule ask admin to check your scheduled shift at this time");                }
                /**
                 * @var Shift $shift
                 */
                if ($shift->getEditable()){//set editable and if its twice to clockin one shift set clock out from last shift
                    $shift->setEditable(false);
                }
                $attendanceTime->setShift($shift);
            }
        }else{//force map shift to selected schedule this means user send shift personally
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
                throw new HttpException('400','u not permit to map shift manually');
            }
            $shift=$attendanceTime->getShift();
            if($shift instanceof Shift )
                $shift->setEditable(false);

        }

    }

        //if shift is creating by admin calulate worked for this shift
        if(!is_null($attendanceTime->getEndTime())){
            $total = (new \DateTime($attendanceTime->getEndTime()))->getTimestamp() -
                (new \DateTime($attendanceTime->getStartTime()))->getTimestamp();
          if (!is_null($attendanceTime->getBreak())){
              $total=$total-(60*$attendanceTime->getBreak());
          }
          if ($total<0){
              throw new HttpException(400,'clock in and exit time is not making sense');
          }
            $attendanceTime->setWorked(floor($total/60));
          //if end time exists in post this means entity register manually by admin
            $attendanceTimeLog=new AttendanceTimesLog();
            $attendanceTimeLog->setUser($attendanceTime->getUser());
            $attendanceTimeLog->setTime($attendanceTime->getStartTime());
            $attendanceTimeLog->setType('warning');
            $attendanceTimeLog->setText('this record recorded by admin manually');
            $this->manager->persist($attendanceTimeLog);

          //if end time exists deserved holiday should calculate
          $this->attendanceService->calculateDeservedHoliday($attendanceTime,$attendanceTime->getUser(),$business);
        }




        //get location and validate comment becuase ip restriction activated

            $location=$attendanceTime->getClockInLocation();
            $attendance_setting=$this->attendanceSettingService->getAttendanceSetting();
            $personal_computer_restriction=$attendance_setting->getRestrictIpforPersonalAttendance();

        if(isset($location) && $this->is_mobile){
                $location = explode(',', $attendanceTime->getClockInLocation());
                $distance = $this->attendanceSettingService->getDistanceFromShift($shift, $location[0], $location[1],$schedule);//check distance from shift
                if ((int)$distance > (int)($setting->getNearByLocationDistance())) {
                    throw new InvalidArgumentException(sprintf('you must be near by %s', $setting->getNearByLocationDistance()));
                }

            }else{
                if (!$this->security->isGranted('BUSINESS_SUPERVISOR') && $this->attendanceSettingService->getAttendanceSetting()->getRestrictIpforPersonalAttendance()  || $this->is_mobile) {
                    throw new InvalidArgumentException('employees cannot clockin without location');
                }
            }

    }

    /**
     * @param AttendanceTimes $attendanceTime
     * @param $user
     * @throws \Exception
     */
    public function prePut($attendanceTime, $user)
    {
        /**
         * @var Business $busineess
         */
        $business=$this->finder->getCurrentUserBusiness();
        //set worked and diff if end time

        if (!is_null($attendanceTime->getEndTime())) {
            $total = (new \DateTime($attendanceTime->getEndTime()))->getTimestamp() -
                (new \DateTime($attendanceTime->getStartTime()))->getTimestamp();
            if (!is_null($attendanceTime->getBreak())) {
                $total = $total - (60 * $attendanceTime->getBreak());
            }
            $attendanceTime->setWorked(floor($total / 60));
            $this->attendanceService->calculateDeservedHoliday($attendanceTime, $attendanceTime->getUser(),$business);


        }
        //save break start and end log
        $this->attendanceService->BreakRegister($this->request_content,$attendanceTime);

        if (array_key_exists('break', $this->request_content)) {
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
                throw new HttpException(400,'you are not permitted to save break manually');
            }
            //calculate worked again for attendance
            if (!is_null($attendanceTime->getEndTime()) && !is_null($attendanceTime->getStartTime())) {
                $total = (new \DateTime($attendanceTime->getEndTime()))->getTimestamp() -
                    (new \DateTime($attendanceTime->getStartTime()))->getTimestamp();
                if (!is_null($attendanceTime->getBreak())) {
                    $total = $total - (60 * $attendanceTime->getBreak());
                }
                $attendanceTime->setWorked(floor($total / 60));

            }

            $log = new AttendanceTimesLog();
            $log->setUser($attendanceTime->getUser());
            $log->setAttendanceTime($attendanceTime);
            $log->setText('attendace break save by admin manually ');
            $log->setType(AttendanceTimesLog::TYPES[0]);
            $log->setTime($attendanceTime->getStartTime());
            $this->manager->persist($log);

        }
        if (array_key_exists('endTime', $this->request_content)) {
            $uow = $this->manager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeSet = $uow->getEntityChangeSet($attendanceTime);
            if (isset($changeSet['endTime']) && is_null($changeSet['endTime'][0])) {
                if (!is_null($attendanceTime->getBreakoutStart())) {
                    $log = new AttendanceTimesLog();
                    $log ->setUser($attendanceTime->getUser());
                    $log->setAttendanceTime($attendanceTime);
                    $log->setTime($attendanceTime->getStartTime());
                    $log->setType(AttendanceTimesLog::TYPES[1]);
                    $log->setText('user forgot register break end');
                    $this->manager->persist($log);
                }
            }
        }
    }

    /**
     * @param AttendanceTimes $attendanceTime
     * @param $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function preDelete($attendanceTime,$user)
    {
        $attendanceLog=new AttendanceTimesLog();
        $attendanceLog->setUser($attendanceTime->getUser());
        $data = $this->serializer->normalize($attendanceTime, null, [AbstractNormalizer::ATTRIBUTES => ['startTime', 'endTime','schedule'=>['name']]]);
        $attendanceLog->setText(json_encode($data).' delete by '.$user);
        $attendanceLog->setType('warning');
        $attendanceLog->setTime($attendanceTime->getStartTime());
        $this->manager->persist($attendanceLog);
        //when an attendance delete deserve holiday also should recomputed
        $this->attendanceService->calculateDeservedHoliday($attendanceTime,$attendanceTime->getUser(),$this->finder->getCurrentUserBusiness());
    }


    /**
     * @param AttendanceTimes $attendanceTime
     */
    public function setTimeZonesAttendance($attendanceTime)
    {
        if(is_null($this->request_content))
            return;

        if (array_key_exists('startTime', $this->request_content)) {
            $attendanceTime->setStartTime($this->timezone->transformUserDateToAppTimezone($this->request_content['startTime']));
        }
        if (array_key_exists('endTime', $this->request_content)) {
            $attendanceTime->setEndTime($this->timezone->transformUserDateToAppTimezone($this->request_content['endTime']));
        }
        if (array_key_exists('breakoutStart', $this->request_content)) {
            $attendanceTime->setBreakoutStart($this->timezone->transformUserDateToAppTimezone($this->request_content['breakoutStart']));
        }
        if (array_key_exists('breakOutEnd', $this->request_content)) {
            $attendanceTime->setBreakOutEnd($this->timezone->transformUserDateToAppTimezone($this->request_content['breakOutEnd']));
        }

    }

    /**
     * @param AttendanceTimes $attendanceTime
     * @return \App\Entity\Schedule|int|string|null
     */
    public function calculateDiff($attendanceTime)
    {
        //find all attendance times except current that has same shift
        $sibling_shift= $this->manager->getRepository(AttendanceTimes::class)->findBy(['shift'=>$attendanceTime->getShift()]);
        //sum worked time of all finded shift
        $worked=0;
        /**
         * @var AttendanceTimes $att
         */
       foreach ($sibling_shift as $att){
          $worked=$att->getWorked()+$worked;
       }

        //deduct summation result from shift start and end
        $diff= $worked - $attendanceTime->getScheduled();
        return $diff;

    }


    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onViewEvent', EventPriorities::PRE_WRITE],
        ];
    }

    public function isMobile()
    {
        return (bool)preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);

    }
}
