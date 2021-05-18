<?php


namespace App\DataFixtures;


use App\Entity\AvailableCountry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Intl\Countries;

class AvailableCountryFixtures extends Fixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
       $country=new AvailableCountry();
       $country->setName("GB");
       $country->setLongName(Countries::getName('GB'));
       $manager->persist($country);


       $manager->flush();
       $this->addReference('gb', $country);

    }

    public function getOrder()
    {
        return 1;
    }
}
