<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\ResourceBundle\LanguageBundle;

class CurrencyFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
         $currency = new Currency();
         $currency->setName('Euro');
         $currency->setCode('EUR');
         $currency->setSymbol(Currencies::getSymbol('EUR'));

         $manager->persist($currency);

        $manager->flush();
        $this->addReference('currency', $currency);

    }

    public function getOrder()
    {
        return 0;
    }
}
