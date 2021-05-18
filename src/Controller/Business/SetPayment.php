<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Billing;
use App\Entity\Business;
use App\Entity\BusinessBank;
use App\Entity\PaymentHistory;
use App\Entity\User;
use App\Service\BankService;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class SetPayment
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var IriConverterInterface
     */
    private $converter;
    /**
     * @var BankService
     */
    private $bankService;

    public function __construct(Security $security,
                                BusinessFinder $finder,
                                EntityManagerInterface $manager,
                                BankService $bankService,
                                IriConverterInterface $converter)
    {
        $this->security = $security;
        $this->finder = $finder;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->bankService = $bankService;
    }

    public function __invoke(Request $request)
    {
        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        /**
         * @var Business $business
         */
        $business=$this->finder->getCurrentUserBusiness();
        $params=json_decode($request->getContent(),true);
        /**
         * @var Billing $billing
         */
        $billing=$this->converter->getItemFromIri($params['billing']);
        $billing_rate=$billing->getCurrency()->getCode();


        //specify rate of billing currency base on supported
        if (!in_array(strtoupper($billing_rate),["aud","cad","dkk","eur","gbp","nzd","sek","usd"])){
            $billing_rate="eur";
        }
        $exchange=$this->bankService->exchangeRate($billing_rate,$billing->getPrice());



        //only a super admin from this business is authorize to pay
        if (!$this->security->isGranted('BUSINESS_ACCOUNT')){
            throw new InvalidArgumentException("only super admin can pay for business");
        }

        if($billing->getId()===$business->getBilling()->getId()){
            throw new InvalidArgumentException("this is your current billing now");
        }

        $client = new \GoCardlessPro\Client(array(
            'access_token' => $_ENV['GC_ACCESS_TOKEN'],
            // Change me to LIVE when you're ready to go live
            'environment' => \GoCardlessPro\Environment::SANDBOX
        ));
        $bankAc=$this->manager->getRepository(BusinessBank::class)
            ->findOneBy(['cancel'=>false,'business'=>$business]);
        if (!isset($bankAc)){
            throw new InvalidArgumentException("set a business bank account for this business first");
        }
        /**
         * @var BusinessBank $bankAc
         */
        $mandate_id=$bankAc->getMandate();
        $mandate = $client->mandates()->get($mandate_id);

        if (intval($params["amount"])!==intval($exchange["amount"])){
            throw new InvalidArgumentException("amount should be same with billing price :)  ".intval($exchange["amount"]));
        }


        $payment = $client->payments()->create([
            "params" => [
                "amount" =>$params["amount"]*100 , // 10 GBP in pence
                "currency" => $params["currency"],
                "links" => [
                    "mandate" => $mandate_id
                    // The mandate ID from last section
                ],
                // Almost all resources in the API let you store custom metadata,
                // which you can retrieve later
                "metadata" => [
                    "billing" => json_encode($billing->getId()),
                    "description"=>"payment for getting new billing"
                ]
            ],
            "headers" => [
                "Idempotency-Key" => $this->generateIdempotency($user,$billing,$business)
            ]
        ]);



// Keep hold of this payment ID - we'll use
// it in a minute
// It should look like "PM000260X9VKF4"

        if ($payment->status==="pending_submission" || $payment->status==="paid_out"|| $payment->status==="submitted"){
            $business->setBilling($billing);
            $this->manager->persist($business);
            $paymentHistory=new PaymentHistory();
            $paymentHistory->setBilling($billing);
            $paymentHistory->setBusiness($business);
            $paymentHistory->setUser($user);
            $paymentHistory->setDescription('payment is create for billing '.$billing->getName());
            $paymentHistory->setStatusOfPayment($payment->status);
            $paymentHistory->setPaymentId($payment->id);
            $this->manager->persist($paymentHistory);
            $this->manager->flush();
            return $paymentHistory;
        }
        else return $payment->status;
    }

    /**
     * @param $user
     * @param $billing
     * @param Business $business
     * @return string
     */
    public function generateIdempotency($user,$billing,$business)
    {
        return $user->getId().$billing->getId().$business->getName().'BID';

    }

}
