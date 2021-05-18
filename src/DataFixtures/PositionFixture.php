<?php

namespace App\DataFixtures;

use App\Entity\Position;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PositionFixture extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $position=new Position();
        $position->setName('first');
        $position->setColor('red');
        $position->setFavorite(false);
        $position->setBusinessId($this->getReference('business'));
        $manager->persist($position);

        $position2=new Position();
        $position2->setName('second');
        $position2->setColor('green');
        $position2->setFavorite(false);
        $position2->setBusinessId($this->getReference('business2'));
        $manager->persist($position2);

        $position3=new Position();
        $position3->setName('third');
        $position3->setColor('blue');
        $position3->setFavorite(false);
        $position3->setBusinessId($this->getReference('business'));

        $manager->persist($position3);

        $manager->flush();
        $this->addReference('position1',$position);
        $this->addReference('position2',$position2);

        /**
         * @var User $user_one
         */
        $user_one=$this->getReference('admin-user');
        $user_one->addPosition($position);
        $manager->persist($user_one);

        /**
         * @var User $user_two
         */
        $user_two=$this->getReference('admin-user2');
        $user_two->addPosition($position2);
        $manager->persist($user_two);

        /**
         * @var User $user_three
         */
        $user_three=$this->getReference('admin-user3');
        $user_three->addPosition($position3);
        $manager->persist($user_three);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 3;
    }
}
