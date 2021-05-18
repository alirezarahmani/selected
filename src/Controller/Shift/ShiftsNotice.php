<?php


namespace App\Controller\Shift;


use App\Entity\AttendanceTimes;
use App\Entity\Shift;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use function Doctrine\ORM\QueryBuilder;

class ShiftsNotice
{
    /**
     * @var
     */
    private $manager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(EntityManagerInterface $manager,SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->serializer = $serializer;
    }

    public function __invoke()
    {
        $notice_array=[];
        $today_shift_qb=$this->manager->createQueryBuilder();

        $today_shift_qb->select('s')
            ->from(Shift::class,'s')
            ->leftJoin('s.attendanceTimes','at')
            ->where($today_shift_qb->expr()->andX(
                $today_shift_qb->expr()->gte('s.startTime',"'".gmdate("Y-m-d 00:000")."'"),
                $today_shift_qb->expr()->lte('s.endTime',"'".gmdate("Y-m-d 23:59")."'")
            ));
        $today_shift_array=$today_shift_qb->getQuery()->execute();

        //FIND LATE CLOCK IN
        /**
         * @var Shift $shift
         */
        foreach ($today_shift_array as $shift){
            $shift_start=new \DateTime($shift->getStartTime());
            $shift_end=new \DateTime($shift->getEndTime());
            $now=new \DateTime();

            $shift_attendance_times=$shift->getAttendanceTimes()->getValues();
            $lastKey = array_key_last($shift_attendance_times);
            if ($now>$shift_start && count($shift_attendance_times)===0){
               $notice_array[]=(array('shift'=>$this->serializer->serialize($shift,'json'),'attendance'=>null,'status'=>'not clock in'));
            }
            /**
             * @var AttendanceTimes $attendance
             */
            foreach ($shift_attendance_times as $key=>$attendance){
                $at_startTime=!is_null($attendance->getStartTime()) ? new \DateTime($attendance->getStartTime()):null;
                $at_endTime=!is_null($attendance->getEndTime())? new \DateTime($attendance->getEndTime()):null;
                if ($shift_end < $now && is_null($at_endTime) ){
                    $notice_array[]=(array('shift'=>$this->serializer->serialize($shift,'json'),'attendance'=>$this->serializer->serialize($attendance,'json'),'status'=>'forgot clock out'));
                }
                if ($key===0){
                    if ($shift_start<$at_startTime){
                        $notice_array[]=(array('shift'=>$this->serializer->serialize($shift,'json'),'attendance'=>$this->serializer->serialize($attendance,'json'),'status'=>'late clock in'));

                    }
                }//it works because ids are auto increment

                if ($key=== $lastKey){        //FIND EARLY CLOCK OUT
                    if ($now > $shift_end && $at_endTime < $shift_end){
                        $notice_array[]=(array('shift'=>$this->serializer->serialize($shift,'json'),'attendance'=>$this->serializer->serialize($attendance,'json'),'status'=>'early clock out'));
                    }
                }

            }

        }

        return new JsonResponse($notice_array,200);

    }


}
