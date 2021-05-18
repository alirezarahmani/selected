<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class UsersController extends AbstractController
{
    private $params;
    public function __construct(ContainerBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @Route("/api/get_avail_roles",name="get_available_roles")
     */
    public function getAvailRole(){
        $array_roles=array_keys($this->params->get('roles'));
        array_shift($array_roles);

        return new JsonResponse($array_roles);
    }

    /**
     * @Route("/api/get_timezone",name="get_timezone")
     */
    public function getTimeZone(){
        return new JsonResponse(timezone_identifiers_list(),200);
    }
    

}
