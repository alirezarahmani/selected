<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\BusinessBank;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

class CompeleteBankAccount
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;

    public function __construct(Security $security,EntityManagerInterface $manager,BusinessFinder $finder)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->finder = $finder;
    }

    public function __invoke(Request $request)
    {
        $params=\GuzzleHttp\json_decode($request->getContent(),true);
        if (!array_key_exists("redirect_flow_id",$params)){
            throw new InvalidArgumentException("redirect_flow_id is required");
        }
        $user=$this->security->getUser();
        //find $user bank flow_id
        $business_banks=$this->manager
            ->getRepository(BusinessBank::class)
            ->findBy(['business'=>$this->finder->getCurrentUserBusiness()]);
        $redirect_correct=false;
        $business_bank=null;
        /**
         * @var BusinessBank$information
         */
        foreach ($business_banks as $information){//to prevent set other customer mandate for this business
            if ($information->getFlowId()===$params["redirect_flow_id"]){
                $redirect_correct=true;
                $business_bank=$information;
                break;
            }
        }
        if (!$redirect_correct){
            throw new UnauthorizedHttpException("cheet","this redirect_flow_id is not belong to you");

        }

        $client = new \GoCardlessPro\Client([
            // We recommend storing your access token in an environment variable for security
            'access_token' => $_ENV['GC_ACCESS_TOKEN'],
            // Change me to LIVE when you're ready to go live
            'environment' => \GoCardlessPro\Environment::SANDBOX
        ]);

        $redirectFlow = $client->redirectFlows()->complete(
            $params["redirect_flow_id"], //The redirect flow ID from above.
            ["params" => ["session_token" =>  $this->security->getToken()->getUsername()]]
        );
        $business_bank->setCustomer($redirectFlow->links->customer);
        $business_bank->setMandate($redirectFlow->links->mandate);
        $business_bank->setCancel(false);


        $customer=$client->customers()->get($redirectFlow->links->customer);
        $country_code=$customer->country_code;

        switch ($country_code){
            case "GB":
                $business_bank->setCurrency("GBP");
                break;
            case "SE":
                $business_bank->setCurrency("SEK");
                break;
            case "DK":
                $business_bank->setCurrency("DKK");
                break;
            case "AU":
                $business_bank->setCurrency("AUD");
                break;
            case "NZ":
                $business_bank->setCurrency("NZD");
                break;
            case "CA":
                $business_bank->setCurrency("CAD");
                break;
            case "US":
                $business_bank->setCurrency("USD");
                break;
            default:
                $business_bank->setCurrency("EUR");
                break;

        }
        $this->manager->persist($business_bank);

        $this->manager->flush();


        if ($redirectFlow->links->customer){
            //cancel last mandates
            /** @var BusinessBank $bs */
            foreach ($business_banks as $bs){
                if ($bs!==$business_bank){
                    $bs->setCancel(true);
                    $this->manager->persist($bs);
                }
            }
        }
        $this->manager->flush();

        return $business_bank;


    }

}
