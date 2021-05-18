<?php


namespace App\Controller\ShiftRequest;


use App\Entity\ShiftRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
//this function give user valid status for accept and deny or cancel a shift request;
class ShiftRequestStatus
{
    public function __invoke()
    {
        return new JsonResponse(ShiftRequest::SHIFT_STATUS,200);
    }


}
