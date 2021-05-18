<?php

namespace App\DataFixtures;

use App\Entity\ShiftTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ShiftTemplateFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $shiftTemplate=new ShiftTemplate();
        $shiftTemplate->setColor('red');
        $shiftTemplate->setPositionId($this->getReference('position1'));
        $shiftTemplate->setStartTime((new \DateTime())->format('h:m'));
        $shiftTemplate->setEndTime((new \DateTime())->format('h:m'));
        $shiftTemplate->setScheduleId($this->getReference('schedule'));
        $shiftTemplate->setNotes('test');

        $manager->persist($shiftTemplate);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 5;
    }
}
