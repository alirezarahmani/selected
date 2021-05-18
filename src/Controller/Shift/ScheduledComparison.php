<?php


namespace App\Controller\Shift;


use App\Entity\Shift;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;

class ScheduledComparison
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var false|string
     */
    private $last_monday;
    /**
     * @var false|string
     */
    private $last_day_last_week;
    /**
     * @var false|string
     */
    private $this_monday;
    /**
     * @var false|string
     */
    private $last_day_this_week;
    /**
     * @var bool
     */
    private $business_id;


    public function __construct(EntityManagerInterface $manager,BusinessFinder $finder)
    {
        $this->manager = $manager;
        $this->finder = $finder;
        $this->last_monday=date('Y-m-d 00:00',strtotime("last week"));
        $this->last_day_last_week=date('Y-m-d 00:00',strtotime("last week +6 day"));
        $this->this_monday= date('Y-m-d 00:00',strtotime("this week"));
        $this->last_day_this_week=date('Y-m-d 23:59',strtotime("this week +6 day"));
        $this->business_id=$this->finder->getUserBusiness();
    }

    public function __invoke()
    {



        $conn=$this->manager->getConnection();
        $SQL="SELECT * FROM ".
             "(SELECT SUM(shift.scheduled) as last_week_scheduled ,SUM(scheduled/60* `ubr`.`base_hourly_rate`) as lastweek_scheduled_wages FROM shift  JOIN `user` ON  shift.owner_id_id=user.id JOIN user_business_role AS `ubr` ON  `ubr`.`user_id`=user.id where `ubr`.`business_id`=".$this->business_id." AND shift.start_time > '".$this->last_monday."' AND shift.end_time < '".$this->last_day_last_week."') AS last_week ,".
             "(SELECT SUM(shift.scheduled) as scheduled ,SUM(scheduled/60* `ubr`.`base_hourly_rate`) as scheduled_wage FROM shift  JOIN `user` ON       shift.owner_id_id=user.id JOIN user_business_role AS `ubr` ON  `ubr`.`user_id`=user.id where `ubr`.`business_id`=".$this->business_id." AND shift.start_time > '".$this->this_monday."' AND shift.end_time < '".$this->last_day_this_week."') AS this_week ";

        $stmt = $conn->prepare($SQL);
        $stmt->execute();

        $result=($stmt->fetchAll());
        $actual_labors_last=$this->calculateLaborsLastWeek();
        $result[0]['actual_wages']=$actual_labors_last['labor'];
        $result[0]['last_week_worked']=$actual_labors_last['last_week_worked'];
        return new JsonResponse($result,200);


    }

    public function calculateLaborsLastWeek()
    {
        $connection=$this->manager->getConnection();
        $SQL_j= " SELECT T1.user_id, sum(worked) as worked , sum(scheduled) as scheduled  FROM
                ( SELECT shift.owner_id_id as `user_id`, 0 as `worked`, sum(shift.scheduled) as `scheduled` FROM `shift` where shift.start_time > '".$this->last_monday."'and shift.end_time <'".$this->last_day_last_week."' and shift.owner_id_id IS NOT NULL  GROUP BY shift.owner_id_id 
                  UNION
                  SELECT attendance_times.user_id , sum(worked) as `worked`, 0 as `scheduled` FROM `attendance_times`  where attendance_times.start_time > '".$this->last_monday."'and attendance_times.end_time <'".$this->last_day_last_week."' AND attendance_times.user_id IS NOT NULL  GROUP BY attendance_times.user_id )
                    AS T1 GROUP BY T1.user_id ";
        $stmt_j = $connection->prepare($SQL_j);
        $stmt_j->execute();
        $attendance_times= $stmt_j->fetchAll();


        $labors=0;
        $last_week_worked=0;

        foreach ($attendance_times as $at){

            $last_week_worked=$last_week_worked+$at['worked'];
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
                    $wage=$user_business_role->getBaseHourlyRate();
                    $wage_ot=$user_business_role->getPayrollOT();
                    $calculate_ot=$user_business_role->getCalculateOT();
                }

            }
            if($calculate_ot){
                $overtime=($at['worked']-$at['scheduled']);
                if ($overtime > 0){
                    $labors=$labors+$overtime/60*$wage_ot+$at['scheduled']/60*$wage;
                }else{
                    $labors=$labors+$at['worked']/60*$wage;
                }


            }else{

                $labors+=$at['worked']/60*$wage;
            }


        }
        return ['labor'=>$labors,'last_week_worked'=>$last_week_worked];


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
