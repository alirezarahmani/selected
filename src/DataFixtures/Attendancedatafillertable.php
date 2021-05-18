<?php

namespace App\DataFixtures;

use App\Entity\Business;
use App\Entity\Shift;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\Timezone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class Attendancedatafillertable extends Fixture implements OrderedFixtureInterface
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,Timezone $timezone)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->timezone = $timezone;
    }

    public function load(ObjectManager $manager)
    {

        /**
         * @var Business $business
         */
        $business=$this->getReference('business');

//        for ($i=0;$i<10; $i++){
//            $rol=$i%2===0 ?'manager':'employee';
//            $u=new User();
//            $u->setEmail('user'.$i.'@gmail.com');
//            $u->setFirstName($i.'user');
//            $u->setLastName($i.'family');
//            $u->setMobile('09378787879');
//            $password2=$this->passwordEncoder->encodePassword($u,'12345678');
//            $u->setPassword($password2);
//            $u->addUserHasSchedule($this->getReference('schedule'));
//            $manager->persist($u);
//            $userBusinessRole=new UserBusinessRole();
//            $userBusinessRole->setBusiness($business);
//            $userBusinessRole->setUser($u);
//            $userBusinessRole->setRole($rol);
//            $manager->persist($userBusinessRole);
//            $this->addReference('user'.$i, $u);
//        }
//        for ($i=0;$i<10;$i++){
//            $shift = new Shift();
//            $shift->setScheduleId($this->getReference('schedule'));
//            $shift->setOwnerId($this->getReference('admin-user'));
//            $time=strtotime('2019-11-'.$i);
//            $shift->setStartTime((new \DateTime('now',new \DateTimeZone($this->timezone->getDefaultTimeZone())))->add(new \DateInterval('P'.$i.'DT'.$i.'H'))->format($this->timezone->getDefaultTimeFormat()));
//            $shift->setEndTime((new \DateTime('now',new \DateTimeZone($this->timezone->getDefaultTimeZone())))->add(new \DateInterval('PT7200S'))->format($this->timezone->getDefaultTimeFormat()));
//            $shift->setPublish(true);
//            $shift->setPositionId($this->getReference('position1'));
//            $shift->setScheduleId($this->getReference('schedule'));
//            $shift->setJobSitesId($this->getReference('jobsite'));
//            $shift->setColor('blue');
//            $manager->persist($shift);
//        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 12;
    }
}
