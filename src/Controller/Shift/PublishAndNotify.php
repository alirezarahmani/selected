<?php


namespace App\Controller\Shift;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\MailsToSend;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\IFTTTHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use function Doctrine\ORM\QueryBuilder;

class PublishAndNotify
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var IriConverterInterface
     */
    private $converter;

    public function __construct(EntityManagerInterface $manager,BusinessFinder $finder,IriConverterInterface $converter)
    {
        $this->manager = $manager;
        $this->finder = $finder;
        $this->converter = $converter;
    }

    public function __invoke(Request $request)
    {
        $params=json_decode($request->getContent(),true);
        $mail_arr=[];
        /**
         * @var Schedule $schedule
         */
        $schedule=$this->converter->getItemFromIri($params['schedule']);
        $users_array=[];

        foreach($params['users'] as $user){
            /**
             * @var User $u
             */
            $u=$this->converter->getItemFromIri($user);
            $users_array[]=$u->getId();
        }
        $start=(new \DateTimeImmutable($params["start"]))->format('Y-m-d 00:00');
        $end=(new \DateTimeImmutable($params['end']))->format('Y-m-d 23:59');
        $queryBuilder=$this->manager->createQueryBuilder();
        $queryBuilder
            ->select('s')
            ->from(Shift::class,'s')
            ->leftJoin('s.scheduleId','sche','sche ='.$schedule->getId())
            ->andwhere(sprintf("s.startTime >= '%s'",$start))
            ->andWhere(sprintf("s.endTime <= '%s'",$end))
            ->andWhere(sprintf("s.publish= %s",true));
        if (count($users_array)){
            $queryBuilder->andWhere($queryBuilder->expr()->in("s.ownerId",$users_array));
        }
        if (array_key_exists('changed_user',$params) && $params['changed_user']){
            $queryBuilder->andWhere($queryBuilder->expr()->eq('s.informed',"0"));
        }


        $shifts=($queryBuilder->getQuery()->getResult());

        /**
         * @var Shift $shift
         */
        foreach ($shifts as $shift){
            if (!is_null($shift->getParentId()) && $shift->getId() === $shift->getParentId()->getId()){//if shift be repeated and be parent
                /**
                 * @var MailsToSend $mail_to_send_parent
                 */
                $mail_to_send_parent=$this->manager->getRepository(MailsToSend::class)->findBy(['parentShift'=>$shift,'status'=>'prepend']);
                if (count($mail_to_send_parent)===0){
                    $messageToSend=new MailsToSend();
                    $messageToSend->setCreatedAT(new \DateTime('now'));
                    $messageToSend->setReceiverEmail($shift->getOwnerId()->getEmail());
                    $messageToSend->addShiftsInMail($shift);
                    $messageToSend->setReceiverFirstName($shift->getOwnerId()->getFirstName());
                    $messageToSend->setReceiverLastName($shift->getOwnerId()->getLastName());
                    $messageToSend->setParentShift($shift);
                    $messageToSend->setStatus(MailsToSend::STATUS[0]);
                    $this->manager->persist($messageToSend);
                    $mail_arr[]=$messageToSend;
                    $shift->setInformed(true);
                    $this->manager->persist($shift);
                }else{
                    $mail_to_send_parent[0]->addShiftsInMail($shift);
                }

            }elseif(!is_null($shift->getParentId()) && $shift->getId() !== $shift->getParentId()->getId()){//if shift be repeated and NOT be parent
                /**
                 * @var MailsToSend $mail_p
                 */
               $mail_p=$this->manager->getRepository(MailsToSend::class)->findBy(['parentShift'=>$shift->getParentId(),'status'=>'prepend']);
               if (count($mail_p)>0){//find parent and parent NOT be null

                   $mail_p[0]->addShiftsInMail($shift);
                   $this->manager->persist($mail_p[0]);
                   $mail_arr[]=$mail_p[0];
                   $shift->setInformed(true);
                   $this->manager->persist($shift);
               }else{//find parent and parent  be null

                   $shift_p=$shift->getParentId();
                   $messageToSend=new MailsToSend();
                   $messageToSend->setCreatedAT(new \DateTime('now'));
                   $messageToSend->setReceiverEmail($shift_p->getOwnerId()->getEmail());
                   $messageToSend->addShiftsInMail($shift_p);
                   $messageToSend->setReceiverFirstName($shift_p->getOwnerId()->getFirstName());
                   $messageToSend->setStatus(MailsToSend::STATUS[0]);
                   $messageToSend->setParentShift($shift_p);
                   $this->manager->persist($messageToSend);
                   $mail_arr[]=$messageToSend;
                   $shift->setInformed(true);
                   $shift_p->setInformed(true);
                   $this->manager->persist($shift_p);
                   $this->manager->persist($shift);
               }

            }else{//shift Not be repeated
                $messageToSend=new MailsToSend();
                $messageToSend->setCreatedAT(new \DateTime('now'));
                $messageToSend->setReceiverEmail($shift->getOwnerId()->getEmail());
                $messageToSend->addShiftsInMail($shift);
                $messageToSend->setStatus(MailsToSend::STATUS[0]);
                $messageToSend->setReceiverFirstName($shift->getOwnerId()->getFirstName());
                $messageToSend->setReceiverLastName($shift->getOwnerId()->getLastName());
                $this->manager->persist($messageToSend);
                $mail_arr[]=$messageToSend;
                $shift->setInformed(true);
                $this->manager->persist($shift);
            }

        }
        $this->manager->flush();
        return new JsonResponse(json_encode($mail_arr),201);

    }


}
