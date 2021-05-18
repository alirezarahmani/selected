<?php


namespace App\Controller\Business;


use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Billing;
use App\Entity\PaymentHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Symfony\Component\HttpFoundation\Request;

class SubmitPayment
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(EntityManagerInterface $manager, \Swift_Mailer $mailer)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public function __invoke(Request $request)
    {

        $super_admins=$this->manager->getRepository(User::class)->findByByRole('ROLE_SUPER_ADMIN');

        $webhook_endpoint_secret=$_ENV["GC_SUBMIT_SECURITY"];

        $request_body = file_get_contents('php://input');

        $headers = getallheaders();
        $signature_header = $headers["Webhook-Signature"];

        try {
            $events = \GoCardlessPro\Webhook::parse($request_body,
                $signature_header,
                $webhook_endpoint_secret);


            // Process the events...
            foreach ($events as $event){

                if ($event->resource_type==="payments"){
                    $links=get_object_vars($event->links);
                    $payment_id=$links["payment"];
                    /**
                     * @var PaymentHistory $payment_history
                     */
                    $payment_history=$this->manager->getRepository(PaymentHistory::class)->findOneBy(['paymentId'=>$payment_id]);
                    if (!isset($payment_history)){
                        foreach ($super_admins as $admin){
                            $message = (new \Swift_Message('welcome to business Email'))
                                ->setFrom('noreply@selectedtime.iwond.com')
                                ->setTo($admin->getEmail())
                                ->setBody('a payment id from gocardless fire an event that not exists in payment history, paymentID:'.$payment_id);
                            $this->mailer->send($message);
                        }
                    }
                    $cause=(get_object_vars($event->details))["cause"];
                    switch ($event->action){
                        case "confirmed":
                            $payment_history->setStatusOfPayment("confirmed");
                            break;
                        case "paid_out":
                            $payment_history->setStatusOfPayment("paid_out");
                            break;
                        case "cancelled":
                        case "failed":
                            $business=$payment_history->getBusiness();
                            /**
                             * @var Billing $billing_default
                             */
                            $billing_default=$this->manager->getRepository(Billing::class)->findOneBy(['isDefault'=>true]);
                            $business->setBilling($billing_default);
                            $this->manager->persist($business);
                            $message = (new \Swift_Message('Your payment was canceled because of '.$cause))
                                ->setFrom('noreply@selectedtime.iwond.com')
                                ->setTo($payment_history->getUser()->getEmail())
                                ->setBody("your payment was failed contact us for more info we downgrade your account to default billing ");
                            $this->mailer->send($message);
                            $payment_history->setStatusOfPayment($event->action);
                            break;
                    }
                }

            }
            header("HTTP/1.1 204 No Content");
        } catch(\GoCardlessPro\Core\Exception\InvalidSignatureException $e) {
            header("HTTP/1.1 498 Invalid Token");
            throw new InvalidArgumentException("hey you, are you kidding me?it seems you want hack us ,next time your business be deactivated");
        }
    }

}
