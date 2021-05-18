<?php


namespace App\Controller\Notification;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\InvalidValueException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Business;
use App\Entity\Notification;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Json;

/**
 * Class AddNotification
 * this is use for add notification group in fire base if not register yet or add device if not register yet
 * @package App\Controller\Notification
 */
class AddNotification
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
     * @var IriConverterInterface
     */
    private $converter;
    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(Security $security,
                                EntityManagerInterface $manager,
                                IriConverterInterface $converter,
                                Notifier $notifier)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->notifier = $notifier;
    }

    public function __invoke(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        if (!isset($params['business'])) {
            throw new InvalidValueException('business is required');
        } else {
            /**
             * @var Business $business
             */
            $business = $this->converter->getItemFromIri($params['business']);
        }
        $businessRole = $this->manager->getRepository(UserBusinessRole::class)->findBy(['user' => $user, 'business' => $business]);
        if (!isset($businessRole)) {
            throw new ItemNotFoundException("business not found in user business");
        }


        $notification_Key_name = $this->notifier->getUserBusinessNotificationKeyName($user, $business);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/notification?notification_key_name=" . $notification_Key_name,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: key=" . $_ENV['FIREBASE_AUTHORIZATION_KEY'],
                "project_id: " . $_ENV['FIREBASE_PROJECT_ID']
            ),
        ));

        $response1 = curl_exec($curl);


        if (curl_errno($curl)===0) {
            $info = curl_getinfo($curl);
            if ($info['http_code'] === 400) {
                //create group
                $data = [
                    "operation" => 'create',
                    "notification_key_name" => $notification_Key_name,
                    "registration_ids" => [$params["registrationtoken"]]
                ];

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://fcm.googleapis.com/fcm/notification",
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
                        "Authorization: key=" . $_ENV['FIREBASE_AUTHORIZATION_KEY'],
                        "project_id: " . $_ENV['FIREBASE_PROJECT_ID'],
                        "Content-Type: text/plain"
                    ),
                ));

                $response = curl_exec($curl);

                if (curl_errno($curl) !== 0) {
                    throw new InvalidValueException($response);
                } else {
                    $notif_response = json_decode($response, true);
                    if (array_key_exists('notification_key', $notif_response)) {
                        $key = $this->joinGroup($notification_Key_name, $notif_response['notification_key'], $params['registrationtoken']);
                        /**
                         * @var Notification $last
                         */
                        $last = $this->manager->getRepository(Notification::class)->findBy(['user' => $user, 'business' => $business]);
                        if (isset($last)) {
                            $last=$last[0];
                            $last->setToken($key);
                            return $last;
                        }else{
                            $notification = new Notification();
                            $notification->setUser($user);
                            $notification->setToken($key);
                            $notification->setBusiness($business);
                            $this->manager->persist($notification);
                            $this->manager->flush();
                            return $notification;
                        }


                    }else{
                        throw new InvalidArgumentException(json_encode($response).' registration ids '.$params["registrationtoken"]);
                    }

                }

            }
            else {
                $notif_response = json_decode($response1, true);
                if (array_key_exists('notification_key', $notif_response))
                    $key = $this->joinGroup($notification_Key_name, $notif_response['notification_key'], $params['registrationtoken']);


                $last = $this->manager->getRepository(Notification::class)->findOneBy(['user' => $user, 'business' => $business]);
                if (isset($last)) {
                    $last->setToken($key);
                    return $last;
                }else{
                    $notification = new Notification();
                    $notification->setUser($user);
                    $notification->setToken($key);
                    $notification->setBusiness($business);
                    $this->manager->persist($notification);
                    $this->manager->flush();
                    return $notification;
                }

            }

        }else{
            throw new HttpException(400,$response1);
        }
        curl_close($curl);
        return new JsonResponse('firebase_error');
    }

    public function joinGroup($notification_key_name, $notification_key, $registration_token)
    {
        $curl = curl_init();

        $data = [
            "operation" => "add",
            "notification_key_name" => $notification_key_name,
            "notification_key" => $notification_key,
            "registration_ids" => [$registration_token]
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/notification",
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
                "Authorization: key=" . $_ENV['FIREBASE_AUTHORIZATION_KEY'],
                "project_id: " . $_ENV['FIREBASE_PROJECT_ID'],
            ),
        ));

        $response = curl_exec($curl);
        $res = json_decode($response, true);
        if (curl_errno($curl) !== 0 || !array_key_exists('notification_key', $res)) {
            throw new InvalidArgumentException($response);
        } else {
            return $res['notification_key'];
        }

    }

}
