<?php


namespace App\Controller;


use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class   Base
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;


    public function __construct(Security $security,ValidatorInterface $validator,ParameterBagInterface $parameterBag){


        $this->validator = $validator;
        $this->security = $security;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return bool
     */
    public function isUserLogin():bool
    {
        if($this->security->getUser()!==null){
            return true;
        }else{
            return false;
        }



    }

    public function validate($data,$group='Default'){
        $errors=$this->validator->validate($data,null,$group);
        $error_array=array();
        if (count($errors))
        {
            /**
             * @var ConstraintViolation $error
             */
            foreach ($errors as $error){
                $error_array[$error->getPropertyPath()]=$error->getMessage();
            }


        }
        return $error_array;
    }

    public function generateToken(User $user){

        $private_key_address=$this->parameterBag->get('secret_key');
        $public_key_address=file_get_contents($this->parameterBag->get('public_key'));
        if (empty($public_key_address)){
            throw new \DomainException('error1:unable to create token');
        }

        $public_key=openssl_pkey_get_public($public_key_address);

        if (!$public_key) {
            throw new \DomainException('error2:unable to create token.');
        }

        $encrypted_data = '';
        $res=openssl_public_encrypt($user->getId()."}}".sha1($user->getPassword()), $encrypted_data, $public_key);
        if (!$res)
            throw new \DomainException('bad params');

        return(base64_encode($encrypted_data));
    }

    public function decryptToken($token)
    {
        $token=base64_decode($token);
        $private_key_address=file_get_contents($this->parameterBag->get('secret_key'));
        if (empty($private_key_address)){
            throw new \DomainException('error1:token exception');
        }
        $private_key=openssl_get_privatekey($private_key_address);
        if (!$private_key_address){
            throw new \DomainException('error12:token exception');
        }
        $information='';
        $res=openssl_private_decrypt($token,$information,$private_key);
        if (!$res)
            throw new \DomainException('token decrypt err');
        return $information;



    }

    public function generateTokenSetPassword(User $user)
    {
        $public_key_address=file_get_contents($this->parameterBag->get('public_key'));
        if (empty($public_key_address)){
            throw new \DomainException('error1:unable to create token');
        }

        $public_key=openssl_pkey_get_public($public_key_address);

        if (!$public_key) {
            throw new \DomainException('error2:unable to create token.');
        }

        $encrypted_data = '';
        $res=openssl_public_encrypt($user->getUsername()."}}", $encrypted_data, $public_key);
        if (!$res)
            throw new \DomainException('bad params');

        return(base64_encode($encrypted_data));
    }


}
