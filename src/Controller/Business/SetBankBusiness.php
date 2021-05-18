<?php


namespace App\Controller\Business;


use App\Entity\BusinessBank;
use App\Entity\User;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class SetBankBusiness
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(Security $security,
                                RouterInterface $router,
                                BusinessFinder $finder,
                                UrlGeneratorInterface $urlGenerator,
                                EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->router = $router;
        $this->finder = $finder;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Request $request)
    {
        if (!$this->security->isGranted("BUSINESS_ACCOUNT")){
            throw new UnauthorizedHttpException("role","your not permitted to get this ");
        }
        $params=json_decode($request->getContent(),true);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host=$request->server->get('SERVER_NAME');


        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        $client = new \GoCardlessPro\Client([
            // We recommend storing your access token in an
            // environment variable for security
            'access_token' => $_ENV['GC_ACCESS_TOKEN'],
            // Change me to LIVE when you're ready to go live
            'environment' => \GoCardlessPro\Environment::SANDBOX
        ]);

        $redirectFlow = $client->redirectFlows()->create([
            "params" => [
                // This will be shown on the payment pages
                "description" => "business",
                // Not the access token
                "session_token" => $this->security->getToken()->getUsername(),
                "success_redirect_url" => $protocol.$host."/success_customer",
                // Optionally, prefill customer details on the payment page
                "prefilled_customer" => [
                    "given_name" => $user->getFirstName(),
                    "family_name" => $user->getLastName(),
                    "email" => $user->getEmail(),
                    "city"=>"London"
                ]
            ]
        ]);
        $id=$redirectFlow->id;
        $business_bank=new BusinessBank();
        $business_bank->setBusiness($this->finder->getCurrentUserBusiness());
        $business_bank->setFlowId($id);
        $this->entityManager->persist($business_bank);
        $this->entityManager->flush();

        return new JsonResponse(["url"=>$redirectFlow->redirect_url,"ID"=> $redirectFlow->id],"200");

    }


}
