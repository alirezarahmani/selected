<?php


namespace App\Controller\AttendanceTimes;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\AttendancePeriod;
use App\Entity\AttendanceTimes;
use App\Entity\Business;
use App\Entity\JobSites;
use App\Entity\Media;
use App\Entity\Schedule;
use App\Entity\TimeOffTotal;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\AttendanceService;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginTerminal
{
    /**
     * @var AttendanceService
     */
    private $attendanceService;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var IriConverterInterface
     */
    private $converter;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(AttendanceService $attendanceService,
                                EntityManagerInterface $manager,
                                IriConverterInterface $converter,
                                Timezone $timezone)
    {
        $this->attendanceService = $attendanceService;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->timezone = $timezone;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * THIS api only can use
     */
    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        if (!array_key_exists('header',$params)|| !array_key_exists('mail',$params)|| !array_key_exists('media',$params)){
            throw new InvalidArgumentException("required params are header and mail not provided");
        }

        /**
         * @var Media $media
         */
        $media=$this->converter->getItemFromIri($params["media"]);
        $media->setConfirmed(true);
        $user_repo=$this->manager->getRepository(User::class);
        /**
         * @var User $user
         */
        $user=$user_repo->findOneBy(['email'=>$params['mail']]);
        $token_encode=$this->attendanceService->validateTerminalToken($params["header"]);

        $schedule_id=$token_encode['schedule'];
        $jobsite_id=$token_encode['jobsite'];
        $business_id=$token_encode["business"];
        $schedule=$this->manager->getRepository(Schedule::class)->find($schedule_id);
        if (!isset($schedule)){
            throw new InvalidArgumentException("token depricate or wrong try again or ask admin set terminal again");
        }
        /**
         * @var JobSites $job_site
         */
        $job_site=$this->manager->getRepository(JobSites::class)->find($jobsite_id);
        if (!isset($job_site)){
            throw new InvalidArgumentException("token depricate or wrong try again or ask admin set terminal again");
        }


        /**
         * @var Business $business
         */
        $business=$this->manager->getRepository(Business::class)->find($business_id);
        if (!isset($business)){
            throw new InvalidArgumentException("token depricate or wrong try again or ask admin set terminal again");
        }
        $location=$job_site->getLat().",".$job_site->getLang();


      if (!isset($user)){
          throw new NotFoundHttpException("user not found");
      }

      $now=$this->timezone->generateSystemDate();
      //check attendancePeriod not be closed
        $qb=$this->manager->createQueryBuilder();
        $qb->select('count(ap.id)')
            ->from(AttendancePeriod::class,'ap')
            ->where($qb->expr()->andX($qb->expr()->gte("'".$now."'",'ap.startTime'),$qb->expr()->lte("'".$now."'",'ap.endTime')))
            ->andWhere($qb->expr()->eq('ap.closed',1));
        $rs=$qb->getQuery()->getSingleScalarResult();
        if ((int)$rs>0){
            throw new HttpException(400,'change in closed date is not permitted');
        }

      //check last row db to see it is clock in or clock out?

        /**
         * @var AttendanceTimes $attendance_last
         */
      $attendance_last=$this->manager->getRepository(AttendanceTimes::class)
          ->findOneBy(['user'=>$user->getId()], ['id' => 'desc']);


      if (!isset($attendance_last)){
          $this->createAttendance($user,$schedule,$location,$media,$business,$now);
          return true;
      }else{
          $endTime=$attendance_last->getEndTime();//if be empty so should be update and this session is for clock out
          if (isset($endTime)){
              $this->createAttendance($user,$schedule,$location,$media,$business,$now);
              return true;
          }

          if (array_key_exists('end_time',$params)||array_key_exists('startTime',$params)){
              $attendance_last->setEndTime($now);
              $attendance_last->setClockOutLocation($location);
              $total = (new \DateTime($now))->getTimestamp() -
                  (new \DateTime($attendance_last->getStartTime()))->getTimestamp();
              if ($total<0){//this is possible if some one add a record for employee in wrong time in future
                  throw new InvalidArgumentException("ask admin to check timesheets there is a open record in future");
              }
              if (!is_null($attendance_last->getBreak())) {
                  $total = $total - (60 * $attendance_last->getBreak());
              }
              $attendance_last->setWorked($total/60);
              $this->attendanceService->calculateDeservedHoliday($attendance_last,$user,$business);


          }
          $this->attendanceService->BreakRegister($params,$attendance_last,true);
          $this->manager->persist($attendance_last);
          $this->manager->flush();
          return true;
      }

    return false;

    }


    private function createAttendance($user,$schedule,$location,$media,$business,$startAttendance)
    {
        $attendance=new AttendanceTimes();
        $attendance->setUser($user);
        $attendance->setStartTime($startAttendance);
        $attendance->setSchedule($schedule);
        $attendance->setClockInLocation($location);
        $attendance->setMedia($media);
        $attendance->setBusiness($business);
        $this->manager->persist($attendance);
        $this->manager->flush();

    }

    public function createAttendanceLog()
    {


    }

}
