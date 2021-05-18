<?php


namespace App\Controller\AttendanceTimes;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\JobSites;
use App\Entity\Schedule;
use App\Service\AttendanceService;
use App\Service\BusinessFinder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SetAdminTokenTerminal
{
    /**
     * @var IriConverterInterface
     */
    private $converter;
    /**
     * @var AttendanceService
     */
    private $attendanceService;
    /**
     * @var BusinessFinder
     */
    private $finder;

    public function __construct(IriConverterInterface $converter,AttendanceService $attendanceService,BusinessFinder $finder)
    {
        $this->converter = $converter;
        $this->attendanceService = $attendanceService;
        $this->finder = $finder;
    }

    public function __invoke(Request $request){


        $params=json_decode($request->getContent(),true);
        if (!array_key_exists('schedule',$params)) {//to get token send schedule is rquired because other can be nullable
            throw new InvalidArgumentException("schedule is required");


        } if (!array_key_exists('jobsite',$params)) {//to get token send schedule is rquired because other can be nullable
            throw new InvalidArgumentException("jobsite is required");
        }

        /**
         * @var Schedule $schedule
         * @var JobSites $jobSite
         */
        $schedule=$this->converter->getItemFromIri($params['schedule']);
        $jobSite=$this->converter->getItemFromIri($params['jobsite']);
        /**
         * @var Business $business
         */
        $business=$this->finder->getCurrentUserBusiness();

        //i create token with private key inreal i just sign token that any one else cannot open it
       $token= $this->attendanceService->generateTerminalToken($schedule->getId(),$jobSite->getId(),$business->getId());
       return new JsonResponse(['token'=>$token]);


    }


}
