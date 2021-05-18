<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;

class SetSuperAdminBankAccount
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        if(!$this->security->isGranted('ROLE_SUPER_ADMIN')){
            throw new InvalidArgumentException('only super admin can register bank account details');
        }

        $client = new \GoCardlessPro\Client(array(
            'access_token' => $_ENV['GC_ACCESS_TOKEN'],
            // Change me to LIVE when you're ready to go live
            'environment' => \GoCardlessPro\Environment::SANDBOX
        ));

        $client->creditors()->create([
            "params" => [
                "name" => $params["name"],
                "address_line1" => $params["address"],
                "city" => $params["city"],
                "postal_code" => $params["zipcode"],
                "country_code" => $params['country']]
        ]);

    }

}
