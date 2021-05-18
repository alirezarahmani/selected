<?php

namespace App\DataFixtures;

use App\Entity\BudgetTools;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BudgetFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $budget=new BudgetTools();
        $budget->setDate((new \DateTime('now',new \DateTimeZone('Europe/London') ))->format('d-m-Y H:i:s') );
        $budget->setBusinessId($this->getReference('business'));
        $budget->setTotal(180);
        $budget->setLabor(80);
        $budget->setScheduleId($this->getReference('schedule'));
        $manager->persist($budget);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
       return 9;
    }
}
