<?php


namespace App\Controller\Users;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class GetUserInfo
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(Security $security,SerializerInterface $serializer,IriConverterInterface $iriConverter)
    {
        $this->security = $security;
        $this->serializer = $serializer;
        $this->iriConverter = $iriConverter;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws  ItemNotFoundException
     */
    public function __invoke(Request $request)
    {
        $user=$this->security->getUser();


        if (empty($user)){
            throw new ItemNotFoundException('user not login');
        }

        $result=$this->serializer->normalize($user,null,["groups"=>"userread"]);
        if(count($result['userBusinessRoles'])>0){
            $roles=&$result['userBusinessRoles'];

            foreach ($roles as &$role){

                $business=$this->iriConverter->getItemFromIri($role['business']);
                $role['name']=$business->getName();

            }
        };
        return new JsonResponse($result,200);
    }
}
