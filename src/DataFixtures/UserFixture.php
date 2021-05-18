<?php

namespace App\DataFixtures;

use App\Entity\EmployeeAlert;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture implements OrderedFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user0=new User();
        $user0->setEmail('moradi.saeed@gmail.com');
        $user0->setFirstName('saeed');
        $user0->setLastName('moradi');
        $user0->setMobile('09378787879');
        $user0->setRoles(['ROLE_SUPER_ADMIN']);
        $password0=$this->passwordEncoder->encodePassword($user0,'12348765');
        $user0->setPassword($password0);


        $user=new User();
        $user->setEmail('ghazaleh@selected.org');
        $user->setFirstName('ghazaleh');
        $user->setLastName('javaheri');
        $user->setMobile('09378787879');


        $password=$this->passwordEncoder->encodePassword($user,'12348765');

        $user->setPassword($password);

        $user2=new User();
        $user2->setEmail('bahareh@selected.org');
        $user2->setFirstName('bahareh');
        $user2->setLastName('hashemi');
        $user2->setMobile('09378787879');
        $password2=$this->passwordEncoder->encodePassword($user2,'12348765');
        $user2->setPassword($password2);

        $user3=new User();
        $user3->setEmail('rafael@selected.org');
        $user3->setFirstName('rafael');
        $user3->setLastName('mendes');
        $user3->setMobile('09378787879');
        $password2=$this->passwordEncoder->encodePassword($user3,'12348765');
        $user3->setPassword($password2);

        $manager->persist($user0);
        $manager->persist($user);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user3);

        $manager->flush();
        $this->addReference('admin-user', $user);
        $this->addReference('admin-user2', $user2);
        $this->addReference('admin-user3', $user3);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
