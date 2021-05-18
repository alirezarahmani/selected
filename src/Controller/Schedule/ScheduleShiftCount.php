<?php


namespace App\Controller\Schedule;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Schedule;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ScheduleShiftCount
{
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(Request $request)
    {
        $request_content=json_decode($request->getContent(),true);
        if (!isset($request_content['schedule']))
            throw new InvalidArgumentException('schedule is required');
        $schedule=$this->iriConverter->getItemFromIri($request_content['schedule']);

        /**
         * @var Schedule $data
         */
       $shifts=$schedule->getShifts();
       return new JsonResponse(array('count'=>count($shifts)));
    }


}
