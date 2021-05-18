<?php


namespace App\Controller\Auth;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Controller\Base;
use App\Entity\User;
use App\Service\MobileConfirmation;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResendVerification extends Base
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MobileConfirmation
     */
    private $confirmation;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(Security $security,
                                ValidatorInterface $validator,
                                ParameterBagInterface $bag,
                                EntityManagerInterface $entityManager,MobileConfirmation $confirmation, \Swift_Mailer $mailer)
    {
        parent::__construct($security,$validator,$bag);
        $this->entityManager = $entityManager;
        $this->confirmation = $confirmation;
        $this->mailer = $mailer;
    }

    public function __invoke(Request $request)
    {
       $params=json_decode($request->getContent(),true);
       $phone=$params['phone'];
       if (!isset($phone))
           throw new InvalidArgumentException('phone number is required');


       $user=$this->entityManager->getRepository(User::class)->findOneBy(['mobile'=>$phone]);
       if (!isset($user))
           throw new InvalidArgumentException('user not found');

       if($this->confirmation->issetVerification($user)){
           $code=$this->confirmation->getCode($user);
       }
       else{
           $code=$this->confirmation->saveCode($user);
       }

        //@todo:send verification code with mobile and sms
        if (!$user->getConfirmWithSms()){
            $token= $this->generateToken($user);
            $message = (new \Swift_Message('Confirm Email'))
                ->setFrom('support@studyfirstgroup.com','selectedTime')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->templating->render(
                    // templates/emails/registration.html.twig
                        'emails/setpassnewer.html.twig',
                        ['name' => $user->getFirstName().' '.$user->getLastName(),'token'=>$this->router->generate('confirm_email',['token'=>$token],RouterInterface::NETWORK_PATH)]
                    ),
                    'text/html'
                )->addPart(
                    $this->templating->render(

                    // templates/emails/registration.txt.twig
                        'emails/setpass.txt.twig',
                        ['name' => $user->getFirstName().' '.$user->getLastName(),'token'=>$this->router->generate('confirm_email',['token'=>$token],RouterInterface::NETWORK_PATH)]
                    ),
                    'text/plain'
                );
            $this->mailer->send($message);
        }else{
            $code=$this->confirmation->saveCode($user);
            SMS::sendSms($code,$user->getMobile());
        }



        return new JsonResponse(['code'=>$code]);
    }

}
