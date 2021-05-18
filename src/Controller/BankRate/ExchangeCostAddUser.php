<?php


namespace App\Controller\BankRate;

use App\Service\BankService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExchangeCostAddUser
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BankService
     */
    private $bankService;

    public function __construct(EntityManagerInterface $manager,BankService $bankService)
    {
        $this->manager = $manager;
        $this->bankService = $bankService;
    }

    /**
     * @param Request $request
     * @return array
     * it returns an array contain cost to pay for add given count user and
     * currency of your current bank account registered in goCardless
     */
    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        $newUsersCount=$params['userCount'];
        $pay=$this->bankService->calculateCostAdditionalUserForBusiness($newUsersCount);
        return $pay;

    }


}
