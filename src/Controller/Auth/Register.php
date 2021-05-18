<?php


namespace App\Controller\Auth;
use ApiPlatform\Core\Bridge\Elasticsearch\Exception\IndexNotFoundException;
use App\Controller\Base;
use App\Entity\EmployeeAlert;
use App\Entity\User;
use App\Service\MobileConfirmation;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Hexanet\Swiftmailer\ImageEmbedPlugin;


class Register extends Base
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Security
     */
    private $security;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var Environment
     */
    private $templating;
    /**
     * @var MobileConfirmation
     */
    private $confirmation;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;


    public function __construct(Security $security,
                                ParameterBagInterface $parameterBag,
                                UserPasswordEncoderInterface $passwordEncoder,
                                ValidatorInterface $validator,
                                EntityManagerInterface $entityManager,
                                RouterInterface $router,
                                Environment $templating,
                                MobileConfirmation $confirmation,
                                \Swift_Mailer $mailer)
    {
        parent::__construct($security,$validator,$parameterBag);
        $this->entityManager = $entityManager;
        $this->passwordEncoder=$passwordEncoder;
        $this->security = $security;
        $this->parameterBag = $parameterBag;
        $this->validator = $validator;
        $this->router = $router;
        $this->templating = $templating;
        $this->confirmation = $confirmation;
        $this->mailer = $mailer;
    }

    public function __invoke(User $data,Request $request)
    {
       $error_array=$this->validate($data,'register');
           if (count($error_array))
           {
               return new JsonResponse(json_encode($error_array),'400',array(),true);
           }

        $employee_alert=new EmployeeAlert();
        $employee_alert->setUserId($data);
        $this->entityManager->persist($employee_alert);


//        $password= random_int(10000000,100000000);
        $new_pwd_encoded = $this->passwordEncoder->encodePassword($data, $data->getPassword());
        $data->setPassword($new_pwd_encoded);
        $this->entityManager->persist($data);
        $this->entityManager->flush();


        if (!$data->getConfirmWithSms()){
           $token= $this->generateToken($data);
            $message = (new \Swift_Message('Confirm Email'))
                ->setFrom('support@studyfirstgroup.com','selectedTime')
                ->setTo($data->getEmail())
                ->setBody(
                    $this->templating->render(
                    // templates/emails/registration.html.twig
                        'emails/setpassnewer.html.twig',
                        ['name' => $data->getFirstName().' '.$data->getLastName(),'token'=>$this->router->generate('confirm_email',['token'=>$token],RouterInterface::NETWORK_PATH)]
                    ),
                    'text/html'
                );
            $this->mailer->registerPlugin(new ImageEmbedPlugin());
            $this->mailer->send($message);
        }else{
            $code=$this->confirmation->saveCode($data);
            SMS::sendSms($code,$data->getMobile());
        }

       return $data;
    }
}
