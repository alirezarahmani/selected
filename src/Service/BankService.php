<?php


namespace App\Service;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\BankRate;
use App\Entity\BusinessBank;
use App\Entity\SelectedTimeGeneralSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class BankService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;

    public function __construct(EntityManagerInterface $manager,BusinessFinder $finder)
    {
        $this->manager = $manager;
        $this->finder = $finder;
    }

    /**
     * @param $billing_currency
     * mony now is in currency one
     * @param $money
     * takes 3 arguments and return mony should be paid in currncy two
     * @return array
     * with keys amount and currency
     * array contain amount to pay and currency to pay
     */
    public function exchangeRate($billing_currency,$money)
    {
        $rates=$this->manager->getRepository(BankRate::class)->find(1);
        $business=$this->finder->getCurrentUserBusiness();
        /**
         * @var BusinessBank $business_bank
         */
        $business_bank=$this->manager->getRepository(BusinessBank::class)->findOneBy(['business'=>$business,'cancel'=>false]);
        if (!isset($business_bank)){
            throw new InvalidArgumentException("please first set your bank details");
        }
        $bank_currency=$business_bank->getCurrency();

        if (!isset($rates)){
            throw new InvalidArgumentException("bank rate is empty ");
        }
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $first_rate=$propertyAccessor->getValue($rates,strtolower($billing_currency));

        $second_rate=$propertyAccessor->getValue($rates,strtolower($bank_currency));

        $pay= round($money/$first_rate*$second_rate,2);
        $symbol=Currencies::getSymbol($bank_currency);
        return ['amount'=>$pay,'currency'=>$bank_currency,'symbol'=>$symbol];

    }

    /**
     * @param $newUsersCount
     * shows how many  user wants to add
     * @return array
     * array keys amount that specify cost to pay for given user
     */
    public function calculateCostAdditionalUserForBusiness($newUsersCount)
    {
        $general_setting=$this->manager->getRepository(SelectedTimeGeneralSettings::class)->findOneBy([]);
        $premium=$general_setting->getPremiumPerUser();
        $pay=$this->exchangeRate('GBP',$premium);

        $amount=$pay["amount"];
        $totalCost=(int)$newUsersCount*(float)$amount;
        $pay=['amount'=>number_format($totalCost,2),'currency'=>$pay['currency'],'symbol'=>$pay['symbol']];
        return $pay;
    }

}
