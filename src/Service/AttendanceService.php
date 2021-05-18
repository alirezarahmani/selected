<?php


namespace App\Service;



use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\AttendanceTimes;
use App\Entity\AttendanceTimesLog;
use App\Entity\Business;
use App\Entity\TimeOffTotal;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;

class AttendanceService
{


    /**
     * @var JWSProviderInterface
     */
    private $provider;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(JWSProviderInterface $provider,EntityManagerInterface $entityManager,Timezone $timezone)
    {
        $this->provider = $provider;
        $this->entityManager = $entityManager;
        $this->timezone = $timezone;
    }

    public function generateTerminalToken($schedule,$jobSites,$business)
    {
      $token=$this->provider->create(["schedule"=>$schedule,"jobsite"=>$jobSites,"business"=>$business]);
      return $token->getToken();

    }

    /**
     * @param string $token
     * @return mixed
     */
    public function validateTerminalToken($token)
    {

        try {
            $decoded= $this->provider->load($token);
            $payload=$decoded->getPayload();

        }catch (\Exception $exception){
            throw new InvalidArgumentException("bad token");
        }
        return ['schedule'=>$payload['schedule'],'jobsite'=>$payload['jobsite'],'business'=>$payload['business']];

    }


    public function calculateTotalClockIN($user,$start,$business)
    {
        $totalClockIn=0;
        $q="SELECT SUM(`worked`) as sum_worked FROM `attendance_times` WHERE `user_id`=".$user->getId()." AND `business_id`=".$business->getId()." AND `start_time` >'".$start."'";
        $stmt = $this->entityManager->getConnection()->prepare($q);
        $stmt->execute();
        $results=($stmt->fetchAll());;
        if (is_array($results) && count($results)>0)
            $totalClockIn=($results);


        return $totalClockIn;

    }

    /**
     * @param AttendanceTimes $attendanceTime
     * @param User $user
     * @param Business $business
     */
    public function calculateDeservedHoliday($attendanceTime,$user,$business)
    {
        //add to total deserved holiday

            $start = date('Y-01-01 00:00');
            $totalClockIn = $this->calculateTotalClockIN($user, $start, $business);


            /** @var UserBusinessRole $userRole */

            $userRole = $user->getUserBusinessRoles()->getValues()[0];
            if ($userRole->getContract() === UserBusinessRole::CONTRACTS[0]) {
                $deserved_minutes = ((int)($totalClockIn[0]["sum_worked"]) * 12.07) / 100;

                $userTotalTimeOffs = $this->entityManager
                    ->getRepository(TimeOffTotal::class)
                    ->findBy(['user' => $attendanceTime->getUser(), 'businessId' => $business->getId()]);
                if (is_array($userTotalTimeOffs) && count($userTotalTimeOffs) > 0) {
                    /** @var TimeOffTotal $userTimeOffTotal */
                    $userTimeOffTotal = $userTotalTimeOffs[0];
                    $userTimeOffTotal->setDeservedHoliday($deserved_minutes);
                    $this->entityManager->persist($userTimeOffTotal);

                }


            }

    }

    /**
     * Check if a given ip is in a network
     * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return boolean true if the ip is in this range / false if not.
     */
    public function ip_in_range( $ip, $range ) {
        if ( strpos( $range, '/' ) == false ) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ip );
        $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }


    /**
     * @param $start
     * @param $end
     * @return float|int number of seconds between two date
     */
    public function calculate_diff_date($start, $end)
    {
        $date1 = strtotime($start);
        $date2 = strtotime($end);
        $diff_seconds = abs($date2 - $date1);
        return floor($diff_seconds / 60);
    }

    /**
     * @param $params
     * @param AttendanceTimes $attendanceTime
     */
    public function BreakRegister($params,$attendanceTime,$set=false)
    {
        if (array_key_exists('breakoutStart',$params)) {
            if (!is_null($attendanceTime->getEndTime())) {
                throw new InvalidArgumentException('you cannot register break start after clock-out');
            }

            $log = new AttendanceTimesLog();
            $log ->setUser($attendanceTime->getUser());
            $log->setAttendanceTime($attendanceTime);
            $log->setTime($attendanceTime->getStartTime());
            $log->setText('break start at '.$attendanceTime->getBreakoutStart());
            $log->setType(AttendanceTimesLog::TYPES[0]);
            $this->entityManager->persist($log);
            $set ? $attendanceTime->setBreakoutStart($this->timezone->generateSystemDate()):"";
        }
        if (array_key_exists('breakOutEnd', $params)) {
            if (!is_null($attendanceTime->getEndTime())) {
                throw new InvalidArgumentException('you cannot register break end after clock-out');
            }
            if (is_null($attendanceTime->getBreakoutStart())){
                $log = new AttendanceTimesLog();
                $log ->setUser($attendanceTime->getUser());
                $log->setAttendanceTime($attendanceTime);
                $log->setTime($attendanceTime->getStartTime());
                $log->setText('forgot to register break start');
                $log->setType(AttendanceTimesLog::TYPES[1]);
                $this->entityManager->persist($log);
            }else{
                $log = new AttendanceTimesLog();
                $log->setUser($attendanceTime->getUser());
                $log->setAttendanceTime($attendanceTime);
                $log->setTime($attendanceTime->getStartTime());
                $log->setText('break end at '.$attendanceTime->getBreakOutEnd());
                $log->setType(AttendanceTimesLog::TYPES[0]);
                $this->entityManager->persist($log);
                $set ? $attendanceTime->setBreakOutEnd($this->timezone->generateSystemDate()):"";
                $diff = $this->calculate_diff_date($attendanceTime->getBreakoutStart(), $attendanceTime->getBreakOutEnd());
                $attendanceTime->setBreak($attendanceTime->getBreak() + $diff);
                $attendanceTime->setBreakoutStart(null);//when ever user register
                // break out end its start wil be 0 and user can know if user forgot register out
            }

        }
        $this->entityManager->persist($attendanceTime);

    }

}
