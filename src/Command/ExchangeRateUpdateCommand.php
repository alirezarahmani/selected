<?php

namespace App\Command;


use App\Entity\BankRate;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Provider\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use \BenMajor\ExchangeRatesAPI\ExchangeRatesAPI;

class ExchangeRateUpdateCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(string $name = null, EntityManagerInterface $manager)
    {
        parent::__construct($name);
        $this->manager = $manager;
    }

    protected static $defaultName = 'exchangeRate:update';

    protected function configure()
    {
        $this
            ->setDescription('this command update exhange rate in database');
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
//        $arg1 = $input->getArgument('arg1');
        $lookup = new ExchangeRatesAPI();
        $res  = $lookup->fetch();
        $rates=$res->getRates();
        $bankrate=new BankRate();
        foreach ($rates as $key=>$rate){
            $k=strtolower($key);
            switch ($k){
                case "aud":
                    $bankrate->setAUD($rate);
                    break;
                case "cad":
                    $bankrate->setCAD($rate);
                    break;
                case "dkk":
                    $bankrate->setDKK($rate);
                    break;
                case "gbp":
                    $bankrate->setGBP($rate);
                    break;
                case "nzd":
                    $bankrate->setNZD($rate);
                    break;
                case "sek":
                    $bankrate->setSEK($rate);
                    break;
                case "usd":
                    $bankrate->setUSD($rate);
                    break;
                default:
                    break;

            }

        }
        $bankrate->setEUR("1");
        $bankrate->setBase($res->getBaseCurrency());
        $dateTime=date_timestamp_set(new \DateTime(),strtotime($res->getTimestamp()));

        $bankrate->setDate($dateTime);
        $this->manager->persist($bankrate);
        $this->manager->flush();

        $io->success('Rate update in db.');

        return 0;
    }
}
