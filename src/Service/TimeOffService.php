<?php


namespace App\Service;


use App\Entity\Business;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use function Doctrine\ORM\QueryBuilder;

class TimeOffService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function totalTimeOffYear($user)
    {
        $date=new \DateTime();
        $year=$date->format('Y');
        $array=['holiday'=>0,"sick"=>0,'unpaid'=>0,'paid'=>0];

        $qb=$this->entityManager->createQueryBuilder();
        $qb->select('tr')
            ->from(TimeOffRequest::class,'tr')
            ->where($qb->expr()->between('tr.endTime',"'".date('Y').'-01-01'."'","'".date('Y').'-12-31'."'"));
        $time_offs=$qb->getQuery()->getResult();
        /**
         * @var TimeOffRequest  $time_off */
        foreach ($time_offs as $time_off){

            $paid_h=$time_off->getPaidHour();
            $array[$time_off->getType()]=$array[$time_off->getType()]+$paid_h;
        }

        return $array;

    }

    /**
     * @param User $user
     * @param $start
     * @param Business $business
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function calculateHourlyEmpTimeOff($user,$start,$business)//claculate total attendance time after start date
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

}
