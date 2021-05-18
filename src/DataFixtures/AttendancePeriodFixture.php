<?php

namespace App\DataFixtures;

use App\Entity\AttendancePeriod;
use App\Entity\Business;
use App\Entity\Shift;
use App\Service\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AttendancePeriodFixture extends Fixture implements OrderedFixtureInterface
{
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(Timezone $timezone)
    {
        $this->timezone = $timezone;
    }

    public function load(ObjectManager $manager)
    {
        /**
         * @var Shift $shift
         * @var Business $business
         */
        $shift=$this->getReference('shift');
        $business=$this->getReference('business');
        $business2=$this->getReference('business2');
        $timestamp_end=60*60*24*365;
        try {
            $start = (new \DateTime($shift->getStartTime(), new \DateTimeZone($this->timezone->getDefaultTimeZone())));
            $end = (new \DateTime($shift->getEndTime(), new \DateTimeZone($this->timezone->getDefaultTimeZone())))->add(new \DateInterval('PT' . $timestamp_end . 'S'));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }



          $interval_date = new \DateInterval('P14D');
                $daterange = new \DatePeriod($start, $interval_date ,$end);
                foreach($daterange as $date){
                   $date_period=new AttendancePeriod();
                   $date_period->setStartTime($date->format('Y-m-d 00:00'));
                   $date_period->setEndTime($date->modify("+".(14-1)."day")->format('Y-m-d 23:59'));
                   $date_period->setBusiness($business);
                   $manager->persist($date_period);

                }


        $interval_date2 = new \DateInterval('P14D');

        $daterange2 = new \DatePeriod($start, $interval_date ,$end);
        foreach($daterange2 as $date){
            $date_period2=new AttendancePeriod();
            $date_period2->setStartTime($date->format('Y-m-d 00:00'));
            $date_period2->setEndTime($date->modify("+".(14-1)."day")->format('Y-m-d 23:59'));
            $date_period2->setBusiness($business2);
            $manager->persist($date_period2);

        }

                $manager->flush();



    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 13;
    }
}
