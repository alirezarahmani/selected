<?php


namespace App\Controller\Auth;


use App\Controller\Base;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetPassword extends Base
{

    /**
     * @var Security
     */
    private $security;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(Security $security,
                                ValidatorInterface $validator,
                                ParameterBagInterface $parameterBag,
                                EntityManagerInterface $entityManager,
                                UserPasswordEncoderInterface $passwordEncoder,
                                \Swift_Mailer $mailer)
    {
        parent::__construct($security, $validator, $parameterBag);
        $this->security = $security;
        $this->validator = $validator;
        $this->parameterBag = $parameterBag;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Request $request)
    {
        $req_contetnt=json_decode($request->getContent(),true);

        $str_info=($this->decryptToken($req_contetnt['token']));
        $info=explode('}}',$str_info);
        if (count($info)<2)
            return new JsonResponse(array('message'=>'bad token code327'),400);
        $user_repo=$this->entityManager->getRepository(User::class);

        //base on user token generation first slice id and second is sh1(password)
        /**
         * @var User $user
         */
        $user=$user_repo->findOneBy(['email'=>$info[0]]);
        $password=$this->passwordEncoder->encodePassword($user,$req_contetnt['password']);
        $user->setPassword($password);
        $this->entityManager->persist($user);
        return $user;

    }


}
