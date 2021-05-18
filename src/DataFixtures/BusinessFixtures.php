<?php

namespace App\DataFixtures;

use App\Entity\AttendanceSetting;
use App\Entity\Billing;
use App\Entity\Business;
use App\Entity\TimeOffTotal;
use App\Entity\UserBusinessRole;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BusinessFixtures extends Fixture implements OrderedFixtureInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $userBusinessRole=new UserBusinessRole();
        $userBusinessRole2=new UserBusinessRole();
        $userBusinessRole3=new UserBusinessRole();
        $userBusinessRole4=new UserBusinessRole();

        $totalTimeOff1=new TimeOffTotal();
        $totalTimeOff2=new TimeOffTotal();
        $totalTimeOff3=new TimeOffTotal();

        $billingRepo=$manager->getRepository(Billing::class);
        $defaultBilling=$billingRepo->findOneBy(['isDefault'=>true]);

        $dateTime = new DateTime();
        $interval = new DateInterval('P1Y');
        $nextYear = $dateTime->add($interval);
        $business=new Business();
        $business->setName('target');
        $business->setCurrency($this->getReference('currency'));
        $business->setAddress('satarkhan');
        $business->setLocation('35.715298,51.404343');
        $business->setBilling($defaultBilling);
        $business->setCountry($this->getReference('gb'));
        $business->setExpireBilling($nextYear->format('d-m-Y H:i:s'));

        $business2=new Business();
        $business2->setName('hadaf');

        $business2->setAddress('satarkhan');
        $business2->setLocation('35.715298,51.404343');
        $business2->setCurrency($this->getReference('currency'));

        $business2->setBilling($defaultBilling);
        $business2->setCountry($this->getReference('gb'));
        $business2->setExpireBilling($nextYear->format('d-m-Y H:i:s'));


        $manager->persist($business);
        $manager->persist($business2);
        $userBusinessRole->setBusiness($business);
        $userBusinessRole->setBaseHourlyRate(12);
        $userBusinessRole->setUser($this->getReference('admin-user'));
        $userBusinessRole->setRole($this->bag->get('roles')['account']);


        $totalTimeOff1->setUser($this->getReference('admin-user'));
        $totalTimeOff1->setBusinessId($business);

        $userBusinessRole2->setBusiness($business2);
        $userBusinessRole2->setBaseHourlyRate(16);
        $userBusinessRole2->setUser($this->getReference('admin-user2'));
        $userBusinessRole2->setRole($this->bag->get('roles')['account']);

        $totalTimeOff2->setUser($this->getReference('admin-user2'));
        $totalTimeOff2->setBusinessId($business2);

        $userBusinessRole3->setBusiness($business);
        $userBusinessRole3->setBaseHourlyRate(12);
        $userBusinessRole3->setUser($this->getReference('admin-user3'));
        $userBusinessRole3->setRole($this->bag->get('roles')['manager']);

        $totalTimeOff3->setUser($this->getReference('admin-user3'));
        $totalTimeOff3->setBusinessId($business);

        $userBusinessRole4->setBusiness($business2);
        $userBusinessRole4->setBaseHourlyRate(10);
        $userBusinessRole4->setUser($this->getReference('admin-user3'));
        $userBusinessRole4->setRole($this->bag->get('roles')['employee']);


        //attendance settings
        $attendanceSetting=new AttendanceSetting();
        $attendanceSetting->setBusiness($business);
        $manager->persist($attendanceSetting);

         $attendanceSetting2=new AttendanceSetting();
        $attendanceSetting2->setBusiness($business2);
        $manager->persist($attendanceSetting2);



        $manager->persist($userBusinessRole);
        $manager->persist($totalTimeOff1);
        $manager->persist($userBusinessRole2);
        $manager->persist($totalTimeOff2);
        $manager->persist($userBusinessRole3);
        $manager->persist($totalTimeOff3);
        $manager->persist($userBusinessRole4);
        $manager->flush();

        $this->addReference('business', $business);
        $this->addReference('business2', $business2);


    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}
