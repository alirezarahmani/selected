<?php
//for single-use jwt use previous user password to create jwt and do not let previous password
//and new password be same


namespace App\Controller\Auth;


use App\Controller\Base;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class ResetRequest extends Base
{
    /**
     * @var EntityManager
     */
    private $entityManager;
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
     * @var Security
     */
    private $security;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var Environment
     */
    private $templating;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager,
                                ValidatorInterface $validator,
                                Security $security,
                                RouterInterface $router,
                                ParameterBagInterface $parameterBag,
                                UserPasswordEncoderInterface $encoder,
                                Environment $templating,
                                \Swift_Mailer $mailer)
    {
        parent::__construct($security,$validator,$parameterBag);
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->mailer = $mailer;
        $this->validator = $validator;
        $this->security = $security;
        $this->router = $router;
        $this->templating = $templating;
        $this->encoder = $encoder;
    }

    public function __invoke($data)
    {
       $user_repo=$this->entityManager->getRepository(User::class);
        /**
         * @var User $user
         */
        $errors=$this->validate($data,'reset_request');
        if (count($errors)){
            return new JsonResponse(json_encode($errors),'400',array(),true);
        }
        $user=$user_repo->findOneBy(["email"=>$data->getEmail()]);
        if ($user!==null){
            $last_seen=$user->getLastResetRequest();

            //check last password request
            if ($last_seen===null){
                $user->setLastResetRequest(new \DateTime());
                $this->entityManager->persist($user);
                $this->entityManager->flush();

            }else{
                $diff = date_diff(new \DateTime(), $user->getLastResetRequest());
               if ((integer)( $diff->format('%i') ) < 0) {
                   return new JsonResponse(json_encode(['message'=>'your last request is processing']),'200',array(),true);
               }
            }

            //create token
//            $token=$this->generateToken($user);
            $password= random_int(10000000,100000000);
            $new_pwd_encoded = $this->encoder->encodePassword($user, $password);
            $user->setPassword($new_pwd_encoded);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $message = (new \Swift_Message('forgot_request'))
                ->setFrom('support@studyfirstgroup.com','selectedTime')
                ->setTo($data->getEmail())
                ->setBody(
                    $this->templating->render(
                    // templates/emails/registration.html.twig
                        'emails/forget-request.html.twig',
                        ['name' => $data->getFirstName().' '.$data->getLastName(),'password'=>$password]
                    ),
                    'text/html'
                )->addPart(
                    $this->templating->render(
                    // templates/emails/registration.txt.twig
                        'emails/forget-request.txt.twig',
                        ['name' => $data->getFirstName().' '.$data->getLastName(),'password'=>$password]
                    ),
                    'text/plain'
                );

            $this->mailer->send($message);
            return new JsonResponse(array('message'=>'email send successfully'),200);





        }else{
            return new JsonResponse(['message'=>'user not found'],404);
        }

    }



}
