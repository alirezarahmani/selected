<?php


namespace App\MessageHandler;
use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\NotificationHistory;
use App\Entity\User;
use App\Message\EmailNotification;
use App\Message\FireBaseNotification;
use App\Message\ParentMassenger;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class MessageHandler implements MessageHandlerInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Notifier
     */
    private $notifier;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(EntityManagerInterface $manager,
                                Notifier $notifier,
                                \Swift_Mailer $mailer,
                                IriConverterInterface $iriConverter)
    {
        $this->manager = $manager;
        $this->notifier = $notifier;
        $this->mailer = $mailer;
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(ParentMassenger $massenger)
    {

        if ($massenger instanceof EmailNotification)
            $this->sendEmail($massenger);

        if ($massenger instanceof FireBaseNotification)
            $this->sendFireBaseNotifcation($massenger);

        /**
         * @var User $user
         */

        $user=$this->manager->getRepository(User::class)->find($massenger->getUserId());
        $notification=new NotificationHistory();
        $notification->setUser($user);
        $notification->setObjectable($massenger->getObjectableIri());
        $notification->setMessage($massenger->getContent());
        $notification->setObjectable($massenger->getObjectableIri());
        if ($massenger instanceof FireBaseNotification)
            $notification->setIsMobile(true);

        $this->manager->persist($notification);
        $this->manager->flush();




    }

    public function sendFireBaseNotifcation(FireBaseNotification $message)
    {
        $curl = curl_init();
        $data = [
            "to" => $message->getNotification(),
            "data" =>array("title"=> $message->getContent()),
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



    }


    public function sendEmail(EmailNotification $message)
    {
        $message = (new \Swift_Message($message->getSubject()))
            ->setFrom('support@studyfirstgroup.com','selectedTime')
            ->setTo($message->getReceiver())
            ->setBody($message->getContent(),
               'text/plain'
            );

        $this->mailer->send($message);

    }


}
