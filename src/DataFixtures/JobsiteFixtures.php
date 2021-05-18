<?php

namespace App\DataFixtures;

use App\Entity\JobSites;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class JobsiteFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $jobsite=new JobSites();
        $jobsite->addSchedule($this->getReference('schedule'));
        $jobsite->setAddress('address1');
        $jobsite->setBusinessId($this->getReference('business'));
        $jobsite->setLang("51.00");
        $jobsite->setLat("35.00");
        $jobsite->setName("jobsite");
        $jobsite->setColor("red");
        $manager->persist($jobsite);

        $jobsite2=new JobSites();
        $jobsite2->addSchedule($this->getReference('schedule2'));
        $jobsite2->setAddress('address1');
        $jobsite2->setBusinessId($this->getReference('business2'));
        $jobsite2->setLang("51.404343");
        $jobsite2->setLat("35.715298");
        $jobsite2->setName("jobsite");
        $jobsite2->setColor("blue");
        $manager->persist($jobsite2);


        $manager->flush();

        $this->addReference('jobsite',$jobsite);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
       return 7;
    }
}
