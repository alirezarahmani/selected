<?php


namespace App\Service;


use App\Entity\Availability;
use App\Entity\Shift;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;

class AvailabilityService
{
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(Timezone $timezone, BusinessFinder $finder, Security $security, EntityManagerInterface $manager)
    {

        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->security = $security;
        $this->manager = $manager;
    }

    /**
     * @param Availability $availability
     * @param $endRepeatedEnd
     * @param null $start_time
     * @return Availability|null
     * @throws \Exception
     */
    public function generateRepeatedAvaialability($availability, $endRepeatedEnd,$start_time=null,$exclude_parent=false)
    {

        $parent = null;
        if ($exclude_parent){//this happen only in edit when current will gonna be $parent
            $parent=$availability;
            $parent->setParentAvailabilityId($parent);
            $this->manager->persist($parent);
        }


        if (is_null($start_time) || $exclude_parent){

            $start_time=$availability->getStartTime();
        }

        //transform ends on repeated
        $days_of_week=[];
        for($i=0;$i<7;$i++){
            $x=jddayofweek($i,2);
            $days_of_week[]=$x;
        }

        $days = explode(',', $availability->getDays());

        if (count($days)===0 || !in_array($days[0],$days_of_week)){
            throw new HttpException(400,'days should not be empty');
        }
        $start_date = new \DateTime($this->timezone->transformSystemDateToUser($start_time));
        $end_date = new \DateTime($this->timezone->transformSystemDateToUser($availability->getEndTime()));
        $end_repeat = new \DateTime($this->timezone->transformSystemDateToUser($endRepeatedEnd));
        $period = iterator_to_array(new \DatePeriod(
            $start_date, // 1st PARAM: start date
            new \DateInterval('P1D'), // 2nd PARAM: interval (1 day interval in this case)
            $end_repeat ,// 3rd PARAM: end date,
            $exclude_parent? \DatePeriod::EXCLUDE_START_DATE:0


        ));


        //calculate distance between start and end
        $availability_start=new \DateTime($this->timezone->transformSystemDateToUser($availability->getStartTime()));
        $diff = $end_date->diff($availability_start);
        $minutes = ($diff->days * 24 * 60) +
            ($diff->h * 60) + $diff->i;


        /**
         * @var \DateTime $date
         */

        $j=0;
        foreach ($period as $date) {

            $day = $date->format('D');

            $timeStamp = $date->getTimestamp();
            $now = strtotime($this->timezone->generateSystemDate());

            if (in_array($day, $days) && $timeStamp > $now) {
                //clone availability
                $availability_child = clone $availability;


                if ($j === 0 && !$exclude_parent) {//ON EXCLUDE PARENT ,PARENT SET  IN UPPER CODE LINE 52
                    $parent = $availability_child;
                }

                //set date for new ones it should be clone so date hour not changes in add
                $new_end_time = clone $date;
                $new_end_time = $new_end_time->add(new \DateInterval('PT' . $minutes . 'M'));

                $availability_child->setStartTime($this->timezone->transformUserDateToAppTimezone($date->format($this->timezone->getDefaultTimeFormat())));
                $availability_child->setEndTime($this->timezone->transformUserDateToAppTimezone($new_end_time->format($this->timezone->getDefaultTimeFormat())));
                $availability_child->setEndReapetedTime($endRepeatedEnd);
                $availability_child->setParentAvailabilityId($parent);
                $this->findConflictedAvailabilityWithShift($availability_child);
                $this->manager->persist($availability_child);
                $j++;

            }
        }
        return $parent;

    }


    /**
     * @param Availability $availability
     */
    public function findConflictedAvailabilityWithShift($availability)
    {

        $start_time=$availability->getStartTime();
        $end_time=$availability->getEndTime();

        $queryBuilder=$this->manager->createQueryBuilder();;

        $queryBuilder->select('shift')
            ->from(Shift::class,'shift')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte("'" . $start_time . "'", 'shift.startTime')
                ,$queryBuilder->expr()->lt("'" . $start_time . "'", 'shift.endTime')))
            ->orWhere($queryBuilder->expr()->andX(
                $queryBuilder->expr()->lte("'" . $end_time . "'", 'shift.endTime')
                ,$queryBuilder->expr()->gt("'" . $end_time . "'", 'shift.startTime')))
            ->orWhere($queryBuilder->expr()->andX(
                $queryBuilder->expr()->lte("'" . $start_time . "'", 'shift.startTime'),
                $queryBuilder->expr()->gte("'" . $end_time . "'", 'shift.startTime')
            ))
            ->andWhere('shift.ownerId =' . $availability->getUser()->getId());
        $conflicted=$queryBuilder->getQuery()->execute();

        if (count($conflicted)>0){
            /**
             * @var Shift $sh
             */
            foreach ($conflicted as $sh){
                $availability->addConflictedShift($sh);
                $this->manager->persist($availability);
            }
        }


    }
}
