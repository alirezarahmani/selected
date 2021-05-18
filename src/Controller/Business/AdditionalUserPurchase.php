<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\BusinessBank;
use App\Entity\PaymentHistory;
use App\Entity\User;
use App\Service\BankService;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class AdditionalUserPurchase
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
     * @var BankService
     */
    private $bankService;

    public function __construct(Security $security,
                                BusinessFinder $finder,
                                EntityManagerInterface $manager,
                                BankService $bankService)
    {
        $this->security = $security;
        $this->finder = $finder;
        $this->manager = $manager;
        $this->bankService = $bankService;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);

        /**
         * @var User $user
         */
        $user_count=$params['userCount'];
        $user=$this->security->getUser();
        $pay=$this->bankService->calculateCostAdditionalUserForBusiness($user_count);
        $amount=$pay['amount'];
        $currency=$pay['currency'];
        /**
         * @var Business $business
         */
        $business=$this->finder->getCurrentUserBusiness();

        //only a super admin from this business is authorize to pay
        if (!$this->security->isGranted('BUSINESS_ACCOUNT')){
            throw new InvalidArgumentException("only super admin can pay for business");
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


        $mandate_id=$bankAc->getMandate();
        $mandate = $client->mandates()->get($mandate_id);
        $payment = $client->payments()->create([
            "params" => [
                "amount" =>$amount*100 , // 10 GBP in pence
                "currency" => $currency,
                "links" => [
                    "mandate" => $mandate_id,
                    // The mandate ID from last section
                ],
                // Almost all resources in the API let you store custom metadata,
                // which you can retrieve later
                "metadata" => [
                    "description"=>"payment for adding new user",
                    "userCount"=>(string)$user_count
                ]
            ],
            "headers" => [
                "Idempotency-Key" => $this->generateIdempotency($user,$business)
            ]
        ]);

        // Keep hold of this payment ID - we'll use
// it in a minute
// It should look like "PM000260X9VKF4"

        if ($payment->status==="pending_submission" || $payment->status==="paid_out"|| $payment->status==="submitted"|| $payment->status==="confirmed"){
            $business->setAdditionalUsersCount((int)$business->getAdditionalUsersCount()+$user_count);
            $this->manager->persist($business);
            $paymentHistory=new PaymentHistory();
            $paymentHistory->setUserCount($user_count);
            $paymentHistory->setBusiness($business);
            $paymentHistory->setUser($user);
            $paymentHistory->setDescription("payment is create for add new user to current billing " );
            $paymentHistory->setStatusOfPayment($payment->status);
            $paymentHistory->setPaymentId($payment->id);
            $this->manager->persist($paymentHistory);
            $this->manager->flush();
            return $paymentHistory;
        }
        return $payment->status;



    }


    public function generateIdempotency($user,$business)
    {
        return $user->getId().$business->getName()."userCount";

    }


}
