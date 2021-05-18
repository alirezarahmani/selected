<?php

namespace App\DataFixtures;

use App\Entity\AttendanceTimes;
use App\Entity\Business;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AttendanceTimesFixtures extends Fixture implements OrderedFixtureInterface
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
        // $product = new Product();
        // $manager->persist($product);
        $attendanceTime=new AttendanceTimes();
        /**
         * @var Business $business
         * @var User $user
         * @var Shift $shift
         * @var Schedule $schedule
         */
        $business=$this->getReference('business');
        $user=$this->getReference('admin-user');
        $shift=$this->getReference('shift');
        $schedule=$this->getReference('schedule');

        $attendanceTime->setBusiness($business);
        $attendanceTime->setUser($user);
        $attendanceTime->setShift($shift);
        $attendanceTime->setClockInLocation('35,51');
        $attendanceTime->setStartTime($shift->getStartTime());
        $out=(new \DateTime($shift->getEndTime(),new \DateTimeZone($business->getTimeZone())))->sub(new \DateInterval('PT600S'))->format($this->timezone->getDefaultTimeFormat());
        $attendanceTime->setEndTime($out);
        $attendanceTime->setBreak(60);
        $attendanceTime->setSchedule($schedule);
        $attendanceTime->setWorked('300');
        $manager->persist($attendanceTime);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 11;
    }
}
