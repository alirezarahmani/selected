<?php


namespace App\Controller\Users;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use App\Controller\Base;
use App\Controller\BusinessChecker;
use App\Entity\Business;
use App\Entity\EmployeeAlert;
use App\Entity\TimeOffTotal;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

//in this class because of voter and default access user is authenticated and in business is supervisor
class AddUser extends Base
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $templating;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(Security $security,
                                EntityManagerInterface $manager,
                                ValidatorInterface $validator,
                                ParameterBagInterface $parameterBag,
                                BusinessFinder $businessFinder,
                                \Swift_Mailer $mailer,
                                UserPasswordEncoderInterface $passwordEncoder,
                                Timezone $timezone,
                                Environment $templating,
                                RouterInterface $router,
                                SerializerInterface $serializer)
    {
        parent::__construct($security,$validator,$parameterBag);
        $this->security = $security;
        $this->manager = $manager;

        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->businessFinder = $businessFinder;
        $this->timezone = $timezone;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User $data
     * @return User|JsonResponse
     */
    public function __invoke($data)
        {
            $system_roles=$this->parameterBag->get('roles');
            $user_role=null;
            $role=$data->getUserBusinessRoles()[0]->getRole();
            if (!array_key_exists($role,$system_roles)){
                throw new InvalidArgumentException(sprintf('role %s not exists',$role));
            }else{
                $user_role=$system_roles[$role];
            }

            /**
             * @var User $newUser
             */


            $errors=$this->validator->validate($data,null,['add']);
            if (count($errors)>0){
                $error_array=[];
                foreach ($errors as $error){
                    $error_array[$error->getPropertyPath()]=$error->getMessage();
                }
                return new JsonResponse($error_array,400);
            }
            /**
             * @var Business $business
             */
            $business=$this->businessFinder->getCurrentUserBusiness();
            //check businiss user limit for add new user
            $number_of_user=$business->getUserBusinessRoles()->getKeys();
            $allowed_user_count=(int)($business->getBilling()->getNumberOfEmployee())+(int)$business->getAdditionalUsersCount();
            if ( $allowed_user_count=== count($number_of_user)){
                throw new InvalidArgumentException("for more user upgrade your plan");
            }
            //superVisor should not be able to add pricing for employess
            if (!$this->security->isGranted('BUSINESS_MANAGER')){
                /**
                 * @var User $data
                 */
                $data->getUserBusinessRoles()[0]->setBaseHourlyRate(0);
                $data->getUserBusinessRoles()[0]->setCalculateOT(false);
                $data->getUserBusinessRoles()[0]->setMaxHoursWeek(0);
                $data->getUserBusinessRoles()[0]->setEditTimeSheet(false);
                $data->getUserBusinessRoles()[0]->setHideInScheduler(false);
            }



            //check if no user with this email create user
            $employee=$this->manager->getRepository(User::class)->findOneBy(['email'=>$data->getEmail()]);
            if (!is_null($employee)){
                //add totalHoliday for new added user
                $totalTimeOff=new TimeOffTotal();
                $totalTimeOff->setUser($employee);
                $totalTimeOff->setBusinessId($this->businessFinder->getCurrentUserBusiness());

                if ($data->getUserBusinessRoles()[0]->getContract()=== UserBusinessRole::CONTRACTS[1]){
                    //one day is 1440minutes and deservedDay for fixed contract for holiday calculate by 5.6*fixedDay
                    // employee work
                    $fixed_day=(int)($data->getUserBusinessRoles()[0]->getFixedDayesContract());
                    $deservedMinutes=5.6*$fixed_day*1440;
                    $totalTimeOff->setDeservedHoliday($deservedMinutes);

                }
                $this->manager->persist($totalTimeOff);

              //send email if user exists and add userbusiness role
                $userBusinessRole=$data->getUserBusinessRoles()[0];
                $userBusinessRole->setBusiness($business);
                $userBusinessRole->setUser($employee);
                $this->manager->persist($userBusinessRole);



                $this->manager->flush();

                $message = (new \Swift_Message('welcome to business Email'))
                    ->setFrom('javaheri.ghazaleh@gmail.com')
                    ->setTo($data->getEmail())
                    ->setBody('welcome to our business login to see');
                $this->mailer->send($message);
                return $employee;
            }else{
                $data->getUserBusinessRoles()[0]->setBusiness($business);
                $data->getUserBusinessRoles()[0]->setUser($data);
                $password= random_int(10000000,100000000);
                $new_pwd_encoded = $this->passwordEncoder->encodePassword($data, $password);
                $data->setPassword($new_pwd_encoded);
                $this->manager->persist($data);
                //add totalHoliday for new added user
                $totalTimeOff=new TimeOffTotal();
                $totalTimeOff->setUser($data);
                $totalTimeOff->setBusinessId($this->businessFinder->getCurrentUserBusiness());

                if ($data->getUserBusinessRoles()[0]->getContract()=== UserBusinessRole::CONTRACTS[1]){
                    //one day is 1440minutes and deservedDay for fixed contract for holiday calculate by 5.6*fixedDay
                    // employee work
                    $fixed_day=(int)($data->getUserBusinessRoles()[0]->getFixedDayesContract());
                    $deservedMinutes=5.6*$fixed_day*1440;
                    $totalTimeOff->setDeservedHoliday($deservedMinutes);

                }
                $this->manager->persist($totalTimeOff);

                //create user alerts preferen
                $employee_alert=new EmployeeAlert();
                $employee_alert->setUserId($data);
                $this->manager->persist($employee_alert);



                $this->manager->flush();
                $token=$this->generateTokenSetPassword($data);
                $message = (new \Swift_Message('set password Email'))
                    ->setFrom('support@studyfirstgroup.com','selectedTime')
                    ->setTo($data->getEmail())
                    ->setBody(
                        $this->templating->render(
                        // templates/emails/registration.html.twig
                            'emails/setpassnewer.html.twig',
                            ['name' => $data->getFirstName().' '.$data->getLastName(),'password'=>$password]
                        ),
                        'text/html'
                    )->addPart(
                        $this->templating->render(

                        // templates/emails/registration.txt.twig
                            'emails/setpass.txt.twig',
                            ['name' => $data->getFirstName().' '.$data->getLastName(),'password'=>$password]
                        ),
                        'text/plain'
                    );

                $this->mailer->send($message);
                return $data;
            }

           //send email for invite $uer

            return $data;
        }

}
