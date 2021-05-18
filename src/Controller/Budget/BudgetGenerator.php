<?php


namespace App\Controller\Budget;
use App\Entity\BudgetTools;
use App\Entity\Business;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class BudgetGenerator
{
    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(BusinessFinder $businessFinder,
                                EntityManagerInterface $manager,
                                Timezone $timezone){

        $this->businessFinder = $businessFinder;
        $this->manager = $manager;
        $this->timezone = $timezone;
    }

    /**
     * @param BudgetTools $data
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke($data,Request $request)
    {
       if (!is_null($data->getTotal()) && $data->getTotal()!=="" && $data->getTotal()!==0){
           /**
            * @var Business $business
            */
           $business=$this->businessFinder->getCurrentUserBusiness();
           $date=$this->timezone->transformUserDateToAppTimezone($data->getDate(),'Y-m-d 24:00');//it just use in date filter and time has no value
           $data_prev=$this->manager->getRepository(BudgetTools::class)->findOneBy(['businessId'=>$business,'date'=>$date,'scheduleId'=>$data->getScheduleId()]);
           if (!empty($data_prev))
               $this->manager->remove($data_prev);

           if ($data->getBusinessId()===null){
               $data->setBusinessId($business);
           }
           $data->setDate($date);
           $this->manager->persist($data);
           $this->manager->flush();
           return $data;
       }
       return new JsonResponse(array('message'=>'not saved no total'),200);

    }

}
