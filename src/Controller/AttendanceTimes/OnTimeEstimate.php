<?php


namespace App\Controller\AttendanceTimes;

use App\Entity\AttendanceTimes;
use App\Entity\Business;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * Class OnTimeEstimate
 * @package App\Controller\AttendanceTimes
 * this class use as callable for api to
 * indicate percentage of on time attendance
 * of login in user last week
 */
class OnTimeEstimate
{
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

    public function __construct(Security $security,
                                EntityManagerInterface $manager,
                                BusinessFinder $finder)
    {

        $this->security = $security;
        $this->manager = $manager;
        $this->finder = $finder;
    }

    public function __invoke(Request $request)
    {
        $params=$request->query->all();

        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        /**
         * @var Business $business
         */
        $business=$this->finder->getUserBusiness();
        /**
         * @var QueryBuilder $qb
         */
        $qb=$this->manager->createQueryBuilder();
        $qb->select('sh')
            ->from(Shift::class,'sh')
            ->join('sh.scheduleId','schedule',Join::WITH)
            ->where($qb->expr()->eq('schedule.businessId',$business))
            ->andWhere($qb->expr()->eq('sh.ownerId',$user->getId()));
        if(isset($params) && is_array($params) && array_key_exists('startTime',$params)){
            $qb->andwhere("sh.startTime >= '".$params['startTime']."'");

        }
        if(isset($params) && is_array($params) && array_key_exists('startTime',$params)){
            $qb->andwhere("sh.endTime <= '".$params['endTime']."'");

        }
        $results=$qb->getQuery()->getResult();
        /**
         * @var Shift $shift
         */
        $counter=0;
        if (!(count($results)>0)){
            return new JsonResponse(['percentage'=> 0]);
        }
        foreach ($results as $shift){
            /**
             * @var AttendanceTimes $attendance
             */
            $attendances=$shift->getAttendanceTimes()->getValues();
            if (count($attendances)>0){
                $attendance=$attendances[0];
                $attend_time=strtotime($attendance->getStartTime());
                $shift_time=strtotime($shift->getStartTime());

                if (($attend_time/60)-($shift_time/60)<=5){
                    $counter++;
            }

            }
            $shift_count=count($results);
            return new JsonResponse(['percentage'=> ($counter/$shift_count)*100]);


        }

    }


}
