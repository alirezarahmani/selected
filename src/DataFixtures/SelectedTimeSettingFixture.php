<?php

namespace App\DataFixtures;

use App\Entity\SelectedTimeGeneralSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SelectedTimeSettingFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
         $generalsetting = new SelectedTimeGeneralSettings();
         $generalsetting->setPremiumPerUser(15);
         $manager->persist($generalsetting);

        $manager->flush();
    }
}
