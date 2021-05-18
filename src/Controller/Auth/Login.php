<?php


namespace App\Controller\Auth;


use Symfony\Component\HttpFoundation\JsonResponse;

class Login
{
   public function __invoke()
   {
       return new JsonResponse(json_encode(array('test'=>'test')),'400',array(),true);

   }


}
