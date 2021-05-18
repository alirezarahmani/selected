<?php


namespace App\Controller\Budget;


use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BudgetPeriodForecast
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    private $business_id;

    public function __construct(EntityManagerInterface $manager,BusinessFinder $finder)
    {

        $this->manager = $manager;
        $this->business_id=$finder->getUserBusiness();

    }

    public function __invoke(Request $request)
    {
       $request_content=json_decode($request->getContent(),true);
       $startTime=$request_content['startTime'];
       $endTime=$request_content['endTime'];
       $date=$request_content['date'];
        $connection=$this->manager->getConnection();
        $SQL_j= " SELECT T1.user_id, sum(worked) as worked , sum(scheduled) as scheduled , sum(scheduled_f) as scheduled_f  FROM
                ( SELECT shift.owner_id_id as `user_id`, 0 as `worked`, sum(shift.scheduled) as `scheduled`,0 as `scheduled_f` FROM `shift` where shift.start_time > '".$startTime."'and shift.end_time <'".$endTime."' and shift.owner_id_id IS NOT NULL  GROUP BY shift.owner_id_id 
                  UNION
                  SELECT shift.owner_id_id , 0 as worked,  0 as `scheduled` ,sum(scheduled) as `schedule_f` FROM `shift`  where shift.start_time > '".$startTime."'and shift.end_time <'".$date."' and shift.owner_id_id IS NOT NULL  GROUP BY shift.owner_id_id 
                  UNION
                  SELECT attendance_times.user_id , sum(worked) as `worked`, 0 as `scheduled`,0 as `schedule_f` FROM `attendance_times`  where attendance_times.start_time > '".$startTime."'and attendance_times.end_time <'".$date."' AND attendance_times.user_id IS NOT NULL  GROUP BY attendance_times.user_id )
                  AS T1 GROUP BY T1.user_id";

        $stmt_j = $connection->prepare($SQL_j);
        $stmt_j->execute();
        $attendance_times= $stmt_j->fetchAll();



        $labors=0;
        $scheduled=0;



        foreach ($attendance_times as $at){
            $wage=0;
            $wage_ot=0;
            $calculate_ot=false;
            $user=$this->manager->getRepository(User::class)->find($at['user_id']);
            /**
             * @var User $user
             * @var UserBusinessRole $user_business_role
             */
            $user_business_roles=($user->getUserBusinessRoles()->getValues());
            foreach ($user_business_roles as $user_business_role){
                if ($user_business_role->getBusiness()->getId() === $this->business_id){
                    $wage=(float)$user_business_role->getBaseHourlyRate();
                    $wage_ot=$user_business_role->getPayrollOT();
                    $calculate_ot=$user_business_role->getCalculateOT();
                }

            }

            $scheduled=$scheduled+($at['scheduled']/60)*$wage;
            if($calculate_ot){
                $overtime=($at['worked']-$at['scheduled_f']);
                if ($overtime > 0){
                    $labors=$wage+$overtime/60*$wage_ot+$at['scheduled']/60*$wage;
                }else{
                    $labors=$wage+$at['worked']/60*$wage;
                }


            }else{

                $labors+=$at['worked']/60*$wage;
            }

        }
        return new JsonResponse(['worked'=>$labors,'scheduled'=>$scheduled],200);



        /**
         *  SELECT user_id, sum(worked) as worked , sum(scheduled) as scheduled FROM ( SELECT shift.owner_id_id as `user_id`, 0 as `worked`, sum(shift.scheduled) as `scheduled` FROM `shift` GROUP BY shift.owner_id_id UNION SELECT user_id , sum(worked) as `worked`, 0 as `scheduled` FROM `attendance_times` GROUP BY user_id )as t GROUP BY user_id

         */

        /**
         * SELECT attendance_times.user_id, sum(shift.scheduled) as scheduled, sum(attendance_times.worked) as worked FROM attendance_times
        LEFT JOIN shift ON attendance_times.shift_id = shift.id
        GROUP BY attendance_times.user_id;
         */


    }



}
