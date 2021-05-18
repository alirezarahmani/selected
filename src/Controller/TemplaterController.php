<?php

namespace App\Controller;

use App\Entity\EmployeeAlert;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\Notifier;
use App\Service\SMS;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use function Doctrine\ORM\QueryBuilder;

class TemplaterController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Notifier
     */
    private $notifier;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(Security $security,
                                Notifier $notifier,
                                Timezone $timezone,
                                EntityManagerInterface $manager)
    {
        $this->security = $security;
        $this->notifier = $notifier;
        $this->manager = $manager;
        $this->timezone = $timezone;
    }

    /**
     * @Route("/api/test")
     */
    public function test()
    {
        $now=date($this->timezone->getDefaultTimeFormat());

        $qb=$this->manager->createQueryBuilder();
        $time=5;
        $till_up=date($this->timezone->getDefaultTimeFormat(),strtotime($now." +".$time." minutes"));

        $shifts=$qb->select('sh')->from(Shift::class,'sh')->where(
            $qb->expr()->andX(
                $qb->expr()->gte("sh.startTime","'".$now."'"),
                $qb->expr()->lt("sh.startTime","'".$till_up."'"))
        )
            ->andWhere($qb->expr()->isNotNull('sh.ownerId'))
            ->getQuery()->getResult();

        $notif_counter=0;
        $email_counter=0;
        /**
         * @var Shift $shift
         */
        foreach ($shifts as $shift){
            /**
             * @var User $user
             */
            $user=$shift->getOwnerId();

            $tmp=$user->getEmployeeAlerts()->getValues();
            /**
             * @var EmployeeAlert $emp_alert
             */
            $emp_alert=$tmp[0];
            if ($emp_alert->getClockReminder()===EmployeeAlert::VALID_VALUES['mobile']){
                $business=$shift->getScheduleId()->getBusinessId();
                $res=$this->notifier->sendNotification($user,Notifier::createMessage('clock in reminder','your shift start in 3 minutes'),$business);

                $notif_counter++;
            }else{
                $email_counter++;
                $this->notifier->sendEmail($user,Notifier::createMessage('clock in reminder','your shift start in 3 minutes'),$shift);
            }
        }

        return new JsonResponse(['shift'=>'test','now'=>$now]);
    }

    /**
     * @Route("/{path}",requirements={"path": "^(?!.*(api|media|cache|resolve|docs)).*$"}, methods={"GET"}, name="Home")
     * @param Request $request
     * @param $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request,$path,Environment $engine)
    {

        if (in_array($path,['forget','register','resetpassword','login','set-password'])){
            return $this->render('pages/Auth/'.$path.'.html.twig');
        }

        if (empty($path)){
            return $this->render('pages/index.html.twig');
        }
        if (!$engine->getLoader()->exists('pages/'.$path.'.html.twig')){
            return $this->render('pages/404.html.twig');
        }

        return $this->render('pages/'.$path.'.html.twig');
    }

    /**
     * @Route("/api/usersendsms")
     */
    public function testfunc()
    {
        SMS::sendSms(123457,"00447506237708");
    }
}
