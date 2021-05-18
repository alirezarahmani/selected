<?php

namespace App\DataFixtures;

use App\Entity\Schedule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ScheduleFixture extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $schedule=new Schedule();
        $schedule->setName('tehran');
        $schedule->setAddress('tehran satarkhan sazman ab');
        $schedule->setLat(35);
        $schedule->setLang(51);
        $schedule->setBusinessId($this->getReference('business'));
        $schedule->addUser($this->getReference('admin-user'));
        $schedule->addUser($this->getReference('admin-user3'));
        $manager->persist($schedule);


        $schedule2=new Schedule();
        $schedule2->setName('brighthon');
        $schedule2->setAddress('tehran2 satarkhan2 sazman2 ab2');
        $schedule2->setLat(35.2);
        $schedule2->setLang(51.2);
        $schedule2->setBusinessId($this->getReference('business2'));
        $schedule2->addUser($this->getReference('admin-user2'));
        $manager->persist($schedule2);

        $schedule3=new Schedule();
        $schedule3->setName('andarzgoo business');
        $schedule3->setAddress('tehran andarzgoo');
        $schedule3->setLat(35);
        $schedule3->setLang(51);
        $schedule3->setBusinessId($this->getReference('business'));
        $schedule3->addUser($this->getReference('admin-user3'));
        $manager->persist($schedule3);

        $manager->flush();
        $this->addReference('schedule',$schedule);
        $this->addReference('schedule2',$schedule2);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 4;
    }
}
