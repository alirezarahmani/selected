<?php


namespace App\Controller\Availability;


use Symfony\Component\HttpFoundation\JsonResponse;

class GetWeekDays
{
    public function __construct()
    {
    }

    public function __invoke()
    {
        $weekDayNames=[];
        for ($i = 0; $i < 7; $i++) {
            $weekDayNames[] = strftime("%a", strtotime("last sunday +$i day"));
        }

        return  new JsonResponse($weekDayNames);
    }

}
