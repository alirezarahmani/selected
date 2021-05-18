<?php


namespace App\Controller\Users;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetShiftCount
{
    //this function shows count of employee shift before update
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $request_content=json_decode($request->getContent(),true);
        if(empty($request_content['user']))
            throw new InvalidArgumentException('id is required ');

        $data=$this->iriConverter->getItemFromIri($request_content['user']);

        return new JsonResponse(['count'=>count($data->getShifts()->toArray())]);
    }


}
