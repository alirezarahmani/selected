<?php


namespace App\Service;


use App\Entity\Position;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use function Doctrine\ORM\QueryBuilder;

class EligiblerShift
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

    public function __construct(EntityManagerInterface $manager,Timezone $timezone,Security $security)
    {
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->security = $security;
    }

    /**
     * @param $start_time
     * @param $end_time
     * @param Schedule $schedule
     * @param Position $position
     * @return mixed
     */
    public function findOpenShiftEligible($start_time,$end_time,$schedule,$position)
    {
        $repo=$this->manager->getRepository(User::class);
        $qb=$repo->createQueryBuilder('u');
        $query=$qb
            ->select('u')
            ->leftJoin('u.positions','p')
            ->leftJoin('u.userHasSchedule','sche')
            ->leftJoin('u.timeOffRequests','tor')
             ->where($qb->expr()->eq('sche.id',$schedule->getId()));
        if (!empty($position))
            $qb->andWhere($qb->expr()->eq('p.id',$position->getId()))
            ;

        $users=$query->getQuery()->execute();

        /**
         * @var User $user
         */
        foreach ($users as $key=> &$user){
            if (count($tr=$user->getTimeOffRequests())>0){
                foreach ($tr as $timeOffRequest){
                    $conflict=$this->timezone->hasConflict($timeOffRequest->getStartTime(),$timeOffRequest->getEndTime(),$start_time,$end_time);
                    if ($conflict && $timeOffRequest->getStatus() === TimeOffRequest::TIME_OFF_ACCEPT){
                        unset($users[$key]);
                    }
                }
            }

            if (count($s=$user->getShifts())>0 ){
                /**
                 * @var Shift $shift;
                 */
                foreach ($s as $shift){
                    $conflict=$this->timezone->hasConflict($shift->getStartTime(),$shift->getEndTime(),$start_time,$end_time);
                    if ($conflict && $shift->getPublish()){
                        unset($users[$key]);
                    }
                }
            }
            //@todo :add availability to this condition chains
        }
        return $users;






    }

    /**
     * @param Shift $shift
     * @return mixed
     */
    public function findSwapShiftEligible($shift)
    {
        $user=$this->security->getUser();
        $shiftRepo=$this->manager->getRepository(Shift::class);
        $shift_start=$shift->getStartTime();
        $shift_end=$shift->getEndTime();
        $now=$this->timezone->generateSystemDate();
        $query_builder=$shiftRepo->createQueryBuilder('s');

        $query_builder
            ->select('s')
            ->distinct()
            ->where($query_builder->expr()->neq('s.ownerId',$user->getId()));

        if (empty($shift->getPositionId())){

            $query_builder->andWhere($query_builder->expr()->isNull('s.positionId'))
                ->andWhere($query_builder->expr()->gt('s.startTime',"'".$now."'"))
                ->andWhere($query_builder->expr()->orX(
                    $query_builder->expr()->gt('s.startTime',"'".$shift_end."'"),
                    $query_builder->expr()->lt('s.endTime',"'".$shift_start."'")
                ));
        }else{
            //not have conflict by
            $query_builder
                ->andWhere($query_builder->expr()->gt('s.startTime',"'".$now."'"))
                ->andWhere($query_builder->expr()->orX(
                    $query_builder->expr()->gt('s.startTime',"'".$shift_end."'"),
                    $query_builder->expr()->lt('s.endTime',"'".$shift_start."'")
                ));
        }
       $shifts=$query_builder->getQuery()->execute();
       return $shifts;







    }

}
