<?php


namespace App\Controller\Auth;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Tests\Compiler\J;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;


class ChangePassword
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(UserPasswordEncoderInterface $encoder,EntityManagerInterface $entityManager)
    {
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request,Security $security)
    {
        $params=json_decode($request->getContent(),true);

        if(empty($params['last_password']) || empty($params['new_password'])){
           return new JsonResponse(json_encode(array('message'=>'bad params')),400);
        }
        //this route is a protected route so user must be login here
        /**
         * @var UserInterface $user
         */
        $user=$security->getUser();
        if($this->encoder->isPasswordValid($user,$params['last_password'])){
            if ($params['new_password']['first']===$params['new_password']['second']){
                $new_pwd_encoded = $this->encoder->encodePassword($user, $params['new_password']['first']);
                $user->setPassword($new_pwd_encoded);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return new JsonResponse(array('message'=>'password changed'),201);

            }else{
                return new JsonResponse(array('message'=>'confirm password should be same new password'),400);
            }

        }


    }


}