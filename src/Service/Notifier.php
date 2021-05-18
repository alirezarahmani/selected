<?php


namespace App\Service;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\AttendanceTimes;
use App\Entity\Availability;
use App\Entity\Business;
use App\Entity\BusinessRequest;
use App\Entity\EmployeeAlert;
use App\Entity\Notification;
use App\Entity\NotificationHistory;
use App\Entity\Schedule;
use App\Entity\ShiftRequest;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Message\EmailNotification;
use App\Message\FireBaseNotification;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Notifier
{

    //manager use case
    const TIME_OFF_REQUEST='timeoff';
    const SHIFT_REQUEST='swapDropShift';
    const NEW_EMPLOYEE='newEmployee';
    const SCHEDULE_UPDATE='scheduleUpdate';
    const USER_AVAILABILITY='availibilityChange';


    //user use case for notification

    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var MessageBusInterface
     */
    private $bus;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * Notifier constructor.
     * @param EntityManagerInterface $manager
     * @param BusinessFinder $finder
     * @param ParameterBagInterface $bag
     * @param IriConverterInterface $iriConverter
     * @param Timezone $timezone
     * @param \Swift_Mailer $mailer
     * @param MessageBusInterface $bus
     */
    public function __construct(EntityManagerInterface $manager,
                                BusinessFinder $finder,
                                ParameterBagInterface $bag,
                                IriConverterInterface $iriConverter,
                                Timezone $timezone,
                                \Swift_Mailer $mailer,
                                MessageBusInterface $bus)
    {

        $this->manager = $manager;
        $this->finder = $finder;
        $this->bag = $bag;
        $this->bus = $bus;
        $this->iriConverter = $iriConverter;
        $this->timezone = $timezone;
        $this->mailer = $mailer;
    }

    /**
     * send notification immediately
     * @param User $user
     * @param $datas
     * this function find notification-key(a uniqdue key that is key of user device Groups) and send given datas
     * as message to user
     * send notification immediatly
     * @param $objectable
     * @param null $business
     * if business set function set notfication to preset group for this business
     * @return bool
     */
    public function sendNotification($user,$datas,$objectable,$business=null)
    {

       if (!isset($business))
          $business=$this->finder->getCurrentUserBusiness();

        //find this business notfication key
        /**
         * @var Notification $current
         */
        $current=$this->manager->getRepository(Notification::class)->findOneBy(['business'=>$business,'user'=>$user]);

        if ($current===null){
//            throw new InvalidValueException("no registration token set for this user");
            return false;
        }

        $notification_key=$current->getToken();


        $curl = curl_init();

        $data = [
            "to" => $notification_key,
            "data" => $datas,
            "priority"=>"high",
            "content-available"=>"on"
        ];


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: key=".$_ENV['FIREBASE_AUTHORIZATION_KEY']
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $notification=new NotificationHistory();
        $notification->setUser($user);
        $notification->setObjectable($objectable);
        $notification->setMessage($datas['body']);
        $notification->setIsMobile(true);
        $this->manager->persist($notification);
        $this->manager->flush();

    }


    /**
     * send email immediately
     * @param User $user
     * @param $data
     * data is generate with Notifier::createMessage
     * send email to user
     * @param $objectable
     * objectable use for notification history persistance,it is the reason of email sent
     */
    public function sendEmail($user,$data,$objectable)
    {
        $message = (new \Swift_Message($data['title']))
            ->setFrom('support@studyfirstgroup.com','selectedTime')
            ->setTo($user->getEmail())
            ->setBody($data['body'],
                'text/plain'
            );

        $this->mailer->send($message);
        $notification=new NotificationHistory();
        $notification->setUser($user);
        $notification->setObjectable($objectable);
        $notification->setMessage($data['body']);
        $notification->setIsMobile(true);
        $this->manager->persist($notification);
        $this->manager->flush();

    }

    /**
     * @param User $user
     * @param Business $business
     * @return mixed
     */
    public function getUserBusinessNotificationKeyName($user,$business)
    {
        return "notification_key_".$user->getId()."_".$business->getName();
    }


    /**
     * @param Object $reason
     * @param string $subject
     * @return bool
     * use this function whenever you want find all system manager and
     * send notification to them base on their alert preference
     */
    public function sendAccountManagerNotification($reason,$subject,$business=null)
    {
        $roles=$this->bag->get('roles');

        if (!isset($business))
            $business=$this->finder->getCurrentUserBusiness();
        $adminsBusinessRoles=$this->manager->getRepository(UserBusinessRole::class)->findBy(['role'=>[$roles['account'],$roles['manager']],'business'=>$business]);
        $content = '';

        $objectable = $this->iriConverter->getIriFromItem($reason);



        switch ($subject){
            case 'swapDropShift':
                /**
                 * @var ShiftRequest $reason
                 */
                $content = 'new shift request by '.$reason->getRequesterId();
                $desc='notify for shift request';

                $user=$reason->getRequesterId();
                /**
                 * @var EmployeeAlert $user_alert
                 */
                $user_alert=$user->getEmployeeAlerts()->getValues()[0];
                if ($user_alert->getSwapDropShift()===EmployeeAlert::VALID_VALUES['mobile']){
                    $user_notification=$this->manager->getRepository(Notification::class)->findOneBy(['user'=>$user,'business'=>$business]);
                    $message=new FireBaseNotification();
                    $message->setContent('your shift requests update');
                    $message->setSubject('your shift request status is on '.$reason->getStatus());
                    $message->setNotification($user_notification->getToken());
                    $message->setUserId($user->getId());
                    $message->setObjectableIri($objectable);

                    $this->bus->dispatch($message);
                }else{
                    $email=$user->getEmail();
                    $message=new EmailNotification();
                    $message->setContent('your shift requests update');
                    $message->setSubject('your shift request status is on '.$reason->getStatus());
                    $message->setReceiver($email);
                    $message->setUserId($user->getId());
                    $message->setObjectableIri($objectable);


                    $this->bus->dispatch($message);
                }

                break;
            case 'timeoff':
                /**
                 * @var TimeOffRequest $reason
                 */
                if ($reason->getStatus()===TimeOffRequest::TIME_OFF_CANCELED){
                    $content='time off cancel by '.$reason->getUserId();
                }else{
                    $content = 'new time off request by '.$reason->getUserId();
                }

                $desc='notify for time off request';
                $user=$reason->getUserId();
                /**
                 * @var EmployeeAlert $user_alert
                 */
                $user_alert=$user->getEmployeeAlerts()->getValues()[0];
                if ($reason->getStatus()!==TimeOffRequest::TIME_OFF_CREATED && $reason->getStatus()!==TimeOffRequest::TIME_OFF_CANCELED){
                    if ($user_alert->getTimeOff()===EmployeeAlert::VALID_VALUES['mobile']){
                        $user_notification=$this->manager->getRepository(Notification::class)->findOneBy(['user'=>$user,'business'=>$business]);
                        $message=new FireBaseNotification();
                        $message->setSubject('yor time offs have  updated');
                        $message->setContent('your time off for '
                            .$this->timezone->transformSystemDateToUser($reason->getStartTime(),$user).' to '
                            .$this->timezone->transformSystemDateToUser($reason->getEndTime(),$user).
                            ' status is on '.$reason->getStatus());
                        $message->setNotification($user_notification->getToken());
                        $message->setUserId($user->getId());
                        $message->setObjectableIri($objectable);

                        $this->bus->dispatch($message);
                    }
                    else {
                        $email=$user->getEmail();
                        $message=new EmailNotification();
                        $message->setSubject('yor time offs have  updated');
                        $message->setContent('your time off for '
                            .$this->timezone->transformSystemDateToUser($reason->getStartTime(),$user).' to '
                            .$this->timezone->transformSystemDateToUser($reason->getEndTime(),$user).
                            ' status is on '.$reason->getStatus());
                        $message->setReceiver($email);
                        $message->setUserId($user->getId());
                        $message->setObjectableIri($objectable);


                        $this->bus->dispatch($message);
                    }


                }


                break;
            case 'scheduleUpdate':
                /**
                 * @var Schedule $reason
                 */
                $content = 'schedule update for schedule '.$reason->getName();
                $desc='notify for schedule updaate';
                break;
            case 'newEmployee':

                if ($reason instanceof BusinessRequest)
                    /**
                     * @var BusinessRequest $reason
                     */
                    $content = 'new employee request to add to business by business request  :'.$reason->getUserId()->getEmail();
                if($reason instanceof User)
                    /**
                     * @var User $reason
                     */
                    $content = 'new employee added to business '.$reason->getEmail();

                 $desc='notify for new user';

                break;
            case 'availibilityChange':
                /**
                 * @var Availability $reason
                 */
                $content = 'user '.$reason->getUser().' set his availability see scheduler for more information';
                $desc='notify for availability';



                break;
                //this should be alert immidiatly
//            case 'clockReminder':
//                /**
//                 * @var AttendanceTimes $desc
//                 */
//                $content = 'shift reminder don`t forget to clock in';
//                $desc='notify for attendance ';
                break;
            default:
                return false;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
        /**
         * @var UserBusinessRole $adminRole
         */
        foreach ($adminsBusinessRoles as $adminRole){
            /**
             * @var User $admin
             */
            $admin=$adminRole->getUser();
            $alerts=$admin->getEmployeeAlerts()->getValues();
            if (count($alerts)>0){
                /**
                 * @var EmployeeAlert $alert
                 */
                $alert=$alerts[0];

                if($propertyAccessor->getValue($alert, $subject)===EmployeeAlert::VALID_VALUES['mobile']){
                    //find user set any notification token
                    /**
                     * @var Notification $notification
                     */
                    $notification=$this->manager->getRepository(Notification::class)->findOneBy(['user'=>$admin,'business'=>$business]);

                    if (isset($notification)){
                        /**
                         * @var FireBaseNotification $message
                         */
                        $message=new FireBaseNotification();
                        $message->setContent($content);
                        $message->setSubject($desc);
                        $message->setNotification($notification->getToken());
                        $message->setUserId($admin->getId());
                        $message->setObjectableIri($objectable);

                        $this->bus->dispatch($message);
                    }else{
                        $email=$admin->getEmail();
                        $message=new EmailNotification();
                        $message->setSubject('notify for set device notification');
                        $message->setContent('from your side no device set for sending notification');
                        $message->setReceiver($email);
                        $message->setUserId($admin->getId());
                        $message->setObjectableIri($objectable);

                        $this->bus->dispatch($message);


                    }

                }
                else {
                    $email=$admin->getEmail();
                    $message=new EmailNotification();
                    $message->setSubject($desc);
                    $message->setContent($content);
                    $message->setReceiver($email);
                    $message->setUserId($admin->getId());
                    $message->setObjectableIri($objectable);

                    $this->bus->dispatch($message);
                }
            }

        }

        return true;

    }



    static public function createMessage($subject,$info,$object=null){
        return array("title"=> $subject,
                     "body"=> $info);
    }

}
