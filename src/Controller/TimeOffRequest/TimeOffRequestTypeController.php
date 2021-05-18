<?php

namespace App\Controller\TimeOffRequest;

use App\Entity\TimeOffRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TimeOffRequestTypeController extends AbstractController
{
   public function __invoke()
   {
       return new JsonResponse(TimeOffRequest::TIME_OFF_TYPE,'200');
   }
}
