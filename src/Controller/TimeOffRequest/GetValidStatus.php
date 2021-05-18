<?php


namespace App\Controller\TimeOffRequest;


use App\Entity\TimeOffRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetValidStatus
{
    public function __invoke()
    {
        return new JsonResponse([TimeOffRequest::TIME_OFF_ACCEPT,TimeOffRequest::TIME_OFF_DENIED,TimeOffRequest::TIME_OFF_CANCELED,TimeOffRequest::TIME_OFF_CREATED
        ],'200');
    }

}
