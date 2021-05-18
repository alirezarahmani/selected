<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;

use App\Entity\Billing;
use Doctrine\Persistence\ObjectManager;

class BillingFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        /**
         * @var Billing $billing
         */
        $billing=new Billing();
        $billing->setCurrency($this->getReference('currency'));
        $billing->setIsDefault(true);
        $billing->setName('default');
        $billing->setNumberOfEmployee(4);
        $billing->setPeriod(365);
        $billing->setUseHiring(false);
        $billing->setUseAttendance(false);
        $billing->setPrice(0);
        $manager->persist($billing);


        $manager->flush();
    }
}
