<?php

namespace App\Controller\Users;


use ApiPlatform\Core\Api\IriConverterInterface;

use App\Entity\Shift;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use App\Service\EligiblerShift;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


//path users/get_openshift_eligible
class EligibleOpenAndReplace {
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     * @var EligiblerShift
     */
    private $eligiblerShift;

    public function __construct(Timezone $timezone,EntityManagerInterface $manager,IriConverterInterface $iriConverter,EligiblerShift $eligiblerShift)
    {
        $this->timezone = $timezone;
        $this->manager = $manager;
        $this->iriConverter = $iriConverter;
        $this->eligiblerShift = $eligiblerShift;
    }

    public function __invoke(Request $request)
    {
        /**
         * @var Shift $data
         */
       $req_content=json_decode($request->getContent(),true);

       $positions=null;
       if (!empty($req_content['positionId']))
            $positions=$this->iriConverter->getItemFromIri($req_content['positionId']);

       $schedule=$this->iriConverter->getItemFromIri($req_content['scheduleId']);
       $start_time=$this->timezone->transformUserDateToAppTimezone($req_content['startTime']);
       $endTime=$this->timezone->transformUserDateToAppTimezone($req_content['endTime']);

      $users=$this->eligiblerShift->findOpenShiftEligible($start_time,$endTime,$schedule,$positions);
      return $users;

    }

}
