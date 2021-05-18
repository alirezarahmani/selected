<?php


namespace App\Controller\Auth;


use App\Controller\Base;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetAction extends Base
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(ParameterBagInterface $parameterBag,
                                ValidatorInterface $validator,
                                Security $security,
                                EntityManagerInterface $entityManager,
                                UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($security,$validator,$parameterBag);
        $this->parameterBag = $parameterBag;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        if(empty($params['plain_password']) || empty($params['token'])){
            return new JsonResponse(json_encode(array('message'=>'bad params')),400,array(),true);
        }
        $str_info=($this->decryptToken($params['token']));
        $info=explode('}}',$str_info);
        if (count($info)<2)
            return new JsonResponse(array('message'=>'bad token code327'),400);
        $user_repo=$this->entityManager->getRepository(User::class);
        //base on user token generation first slice id and second is sh1(password)
        /**
         * @var User $user
         */
        $user=$user_repo->find($info[0]);
        if (!empty($user)){
           if( sha1($user->getPassword())===$info[1]){
               $array_password=$params['plain_password'];
               $password=$this->passwordEncoder->encodePassword($user,$array_password['first']);
               if( $array_password['first']===$array_password['second'] && sha1($password)!==sha1($user->getPassword())){

                   $user->setPassword($password);
                   $this->entityManager->persist($user);
                   $this->entityManager->flush();
                   return new JsonResponse(array('message'=>'password change'),201);
               }else{
                   return new JsonResponse(array('message'=>'last password should not be use'),400);
               }
           }else{
               return new JsonResponse(['message'=>'token not found'],400);
           };
        }
    }


}
