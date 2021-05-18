<?php


namespace App\Controller\BankRate;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Billing;
use App\Entity\BusinessBank;
use App\Service\BankService;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class ExchangeBillingToCustomer
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var IriConverterInterface
     */
    private $converter;
    /**
     * @var BankService
     */
    private $bankService;

    public function __construct(EntityManagerInterface $manager,
                                Security $security,
                                BankService $bankService,
                                IriConverterInterface $converter,
                                BusinessFinder $finder)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->finder = $finder;
        $this->converter = $converter;
        $this->bankService = $bankService;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        $billing_iri=$params['billing'];
        /**
         * @var Billing $billing
         */
        $billing=$this->converter->getItemFromIri($billing_iri);
        $billing_rate=$billing->getCurrency()->getCode();


        //specify rate of billing currency base on supported
        if (!in_array(strtoupper($billing_rate),["aud","cad","dkk","eur","gbp","nzd","sek","usd"])){
            $billing_rate="eur";
        }

        $pay=$this->bankService->exchangeRate($billing_rate,$billing->getPrice());

       return $pay;

    }


}
