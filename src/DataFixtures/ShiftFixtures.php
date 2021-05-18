<?php

namespace App\DataFixtures;

use App\Entity\Shift;
use App\Service\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Provider\DateTime;

class ShiftFixtures extends Fixture implements  OrderedFixtureInterface
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
        $startTime=new \DateTime();
        $endTime=$startTime->add(new \DateInterval('PT1H'));
        for($i=0;$i<50;$i++){

        }
         $shift = new Shift();
         $shift->setScheduleId($this->getReference('schedule'));
         $shift->setOwnerId($this->getReference('admin-user'));
         $I=5;
         $shift->setStartTime((new \DateTime('now',new \DateTimeZone($this->timezone->getDefaultTimeZone())))->format($this->timezone->getDefaultTimeFormat()));
         $shift->setEndTime((new \DateTime('now',new \DateTimeZone($this->timezone->getDefaultTimeZone())))->add(new \DateInterval('P0DT'.$I.'H'))->format($this->timezone->getDefaultTimeFormat()));
         $shift->setPublish(true);
         $shift->setUnpaidBreak(10);
         $shift->setPositionId($this->getReference('position1'));
         $shift->setScheduled(290);
         $shift->setScheduleId($this->getReference('schedule'));
         $shift->setJobSitesId($this->getReference('jobsite'));
         $shift->setColor('red');

         $manager->persist($shift);

         $this->addReference('shift',$shift);


        // $manager->persist($product);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
       return 8;
    }
}
