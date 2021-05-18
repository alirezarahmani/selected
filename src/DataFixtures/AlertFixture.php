<?php

namespace App\DataFixtures;

use App\Entity\EmployeeAlert;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AlertFixture extends Fixture Implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
       $alert=new EmployeeAlert();
       $alert->setUserId($this->getReference('admin-user'));
       $manager->persist($alert);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 10;
    }
}
