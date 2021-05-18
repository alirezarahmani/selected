<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Business;
use App\Entity\ShiftHistory;
use App\Entity\ShiftRequest;
use App\Entity\ShiftRequestLog;
use App\Entity\SwapUserShiftAccept;
use App\Entity\User;
use App\Service\BusinessFinder;
use App\Service\EligiblerShift;
use App\Service\Notifier;
use App\Service\Timezone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

class ShiftRequestWriteSubscriber implements EventSubscriberInterface
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
     * @var Timezone
     */
    private $timezone;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EligiblerShift
     */
    private $eligiblerShift;
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * ShiftRequestWriteSubscriber constructor.
     * @param Security $security
     * @param EntityManagerInterface $manager
     * @param Timezone $timezone
     * @param BusinessFinder $finder
     * @param EligiblerShift $eligiblerShift
     * @param Notifier $notifier
     */
    public function __construct(Security $security,
                                EntityManagerInterface $manager,
                                Timezone $timezone,
                                BusinessFinder $finder,
                                EligiblerShift $eligiblerShift,
                                Notifier $notifier)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->eligiblerShift = $eligiblerShift;
        $this->notifier = $notifier;
    }

    public function ShiftRequestPrePost(ViewEvent $event)
    {
        $shiftRequest=$event->getControllerResult();
        $method=$event->getRequest()->getMethod();
        if (!$shiftRequest instanceof ShiftRequest) {
            return;
        }
        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        if ($method === Request::METHOD_POST){
            if($shiftRequest->getType() === ShiftRequest::REPLACE  || $shiftRequest->getType() === ShiftRequest::Drop)
                $this->createShiftReplace($shiftRequest,$user);

            if($shiftRequest->getType() === ShiftRequest::SWAP )
                $this->createShiftSwap($shiftRequest,$user);
        }
        if ($method === Request::METHOD_PUT){
            if($shiftRequest->getType() === ShiftRequest::SWAP)
                $this->updateShiftRequestSwap($shiftRequest,$user);

            if($shiftRequest->getType() === ShiftRequest::REPLACE)
                $this->updateShiftRequestReplace($shiftRequest,$user);
        }

    }

    /**
     * @param ShiftRequest $shiftRequest
     */
    public function createShiftReplace($shiftRequest,$user)
    {
        //here $request is for drop or replace and so at least one user is for replacing or drop els action is not permitted
        $shift=$shiftRequest->getRequesterShift();
        $type=$shiftRequest->getType();
        //validate
        if ($type === ShiftRequest::REPLACE && !$this->security->isGranted('BUSINESS_SUPERVISOR') )
            throw new InvalidArgumentException('user is not authorize to request for replace');
        if ($type === ShiftRequest::Drop && $shift->getOwnerId() !== $user)
            throw new InvalidArgumentException('user is not authorize to request for drop');
        //set Status
        if ($this->security->isGranted('BUSINESS_SUPERVISOR')){
            $shiftRequest->setStatus(ShiftRequest::SHIFT_STATUS[2]);
        }else{
            $shiftRequest->setStatus(ShiftRequest::SHIFT_STATUS[4]);

        }
        $shiftRequest->setBusinessId($this->finder->getCurrentUserBusiness());
        $shiftRequest->setRequesterId($user);
        $shiftRequest->setDate($this->timezone->generateSystemDate());



        //check for eligible swap it should not be empty
        if (count($shiftRequest->getSwaps()) === 0){
            $eligible=$this->eligiblerShift->findOpenShiftEligible($shift->getStartTime(),$shift->getEndTime(),$shift->getScheduleId(),$shift->getPositionId());
            if (count($eligible) ===0)
                throw new InvalidArgumentException('there is no one to accept this drop');
            $this->manager->persist($shiftRequest);
            foreach ($eligible as $e){
                $swap=new SwapUserShiftAccept();
                $swap->setUser($e);
                $swap->setShiftRequest($shiftRequest);
                $this->manager->persist($swap);
                //send notification
                $this->notifier->sendNotification($e,Notifier::createMessage('shift request','admin send you a replace request ',$shiftRequest));
            }
        }else{
            $swaps=$shiftRequest->getSwaps();
            /**
             * @var SwapUserShiftAccept $swap
             */
            foreach ($swaps as $swap){
                $user=$swap->getUser();
                //send notification
                $this->notifier->sendNotification($user,Notifier::createMessage('shift request','admin send you a replace request ',$shiftRequest));

            }
            $this->manager->persist($shiftRequest);

        }

        $shiftRequestLog=new ShiftRequestLog();

        $shiftRequestLog->setShiftRequestId($shiftRequest);
        $shiftRequestLog->setCreatorId($user);
        //it should be show status of shift request
        $shiftRequestLog->setType($shiftRequest->getStatus());
        //every message on create save as shiftRequestLog message
        $shiftRequestLog->setMessage($shiftRequest->getMessage());
        $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
        $this->manager->persist($shiftRequestLog);


    }

    /**
     * @param ShiftRequest $shiftRequest
     * @param $user
     */
    public function updateShiftRequestReplace($shiftRequest,$user)
    {
        $swaps=$shiftRequest->getSwaps();
        $shift=$shiftRequest->getRequesterShift();
        $status=$shiftRequest->getStatus();
        //validate
        //accepted request could not be changed
        /**
         * @var UnitOfWork $uow
         */
        $uow = $this->manager->getUnitOfWork();
        $uow->computeChangeSet($this->manager->getClassMetadata(ShiftRequest::class),$shiftRequest);
        $changeSet = $uow->getEntityChangeSet($shiftRequest);
        foreach ($changeSet as $key=>$changes){

           if ($key === 'status' && $changes[0] === ShiftRequest::SHIFT_STATUS[0])
               throw  new UnauthorizedHttpException('role','no one can change acepted shift request satus');

        }

        switch ($status){
            case 'denied':
            case 'approve':
                if (!$this->security->isGranted('BUSINESS_SUPERVISOR'))
                    throw new UnauthorizedHttpException('role','you are not permitted to approve');
                $shiftRequestLog=new ShiftRequestLog();
                $shiftRequestLog->setShiftRequestId($shiftRequest);
                $shiftRequestLog->setCreatorId($user);
                $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
                $shiftRequestLog->setType($shiftRequest->getStatus());
                $this->manager->persist($shiftRequestLog);
                break;
            case 'accept' :
                $in_array=false;
                $this->notifier->sendAccountManagerNotification($shiftRequest,Notifier::SHIFT_REQUEST);
                foreach ($swaps as $swap){
                    if ($swap->getUser() === $user){
                        $in_array=true;
                        $swap->setStatus(true);
                        $this->manager->persist($swap);
                    }
                }
                if (!$in_array)
                    throw new InvalidArgumentException('you cannot accept this replace');
                //set log for shift request
                $shiftRequestLog=new ShiftRequestLog();
                $shiftRequestLog->setType($shiftRequest->getStatus());
                $shiftRequestLog->setCreatorId($user);
                $shiftRequestLog->setShiftRequestId($shiftRequest);
                $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
                $this->manager->persist($shiftRequestLog);
                //set history for replaced shift
                $shiftOwner=$shift->getOwnerId();
                $shift->setOwnerId($user);
                $shiftHistory=new ShiftHistory();
                $shiftHistory->setType(ShiftHistory::SHIFT_UPDATE);
                $shiftHistory->setShiftId($shift);
                $shiftHistory->setUserId($user);
                $shiftHistory->setDate($this->timezone->generateSystemDate());
                $shiftHistory->setChangedProperty(sprintf('shift owner Id chnaged from %s to %s',$shiftOwner,$user));
                $this->manager->persist($shiftHistory);
                break;
            case  'decline':
                $in_array=false;
                $remain=[];
                foreach ($swaps as $swap){
                    if ($swap->getUser() === $user){
                        $in_array=true;
                        $swap->setStatus(false);
                        $this->manager->persist($swap);
                    }

                    elseif(empty($swap->getStatus())){
                        $remain[]=$swap->getUser();
                    };
                }
                if (!$in_array)
                    throw new InvalidArgumentException('you cannot accept this replace');

                if (count($remain) === 0)
                    $shiftRequest->setStatus('decline');

                break;
            case 'cancel':
                if ($user !== $shiftRequest->getRequesterId()){
                    throw new UnauthorizedHttpException('role','you are not permitted to cancel others shift');
                }
                $shiftRequestLog=new ShiftRequestLog();
                $shiftRequestLog->setShiftRequestId($shiftRequest);
                $shiftRequestLog->setCreatorId($user);
                $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
                $shiftRequestLog->setType($shiftRequest->getStatus());
                $this->manager->persist($shiftRequestLog);

            default:
                throw new InvalidArgumentException('bad status');
        }


    }

    /**
     * @param ShiftRequest $shiftRequest
     */
    public function createShiftSwap($shiftRequest,$user)
    {
        $shift=$shiftRequest->getRequesterShift();
        $swaps=$shiftRequest->getSwaps();
        //validate
        if ($shiftRequest->getRequesterShift()->getOwnerId() !== $user){
            throw new UnauthorizedHttpException('ro;e','you are not permit to swap this shift');
        }
        if (count($swaps) === 0){
            throw new InvalidArgumentException('you should specify shift to swap');
        }
        //set shift request
        $shiftRequest->setBusinessId($this->finder->getCurrentUserBusiness());
        $shiftRequest->setDate($this->timezone->generateSystemDate());
        $shiftRequest->setDate($this->timezone->generateSystemDate());
        $shiftRequest->setRequesterId($user);



        if ($this->security->isGranted('BUSINESS_SUPERVISOR')){
            $shiftRequest->setStatus(ShiftRequest::SHIFT_STATUS[2]);
        }else{
            $shiftRequest->setStatus(ShiftRequest::SHIFT_STATUS[4]);
        }

        /**
         * @var SwapUserShiftAccept $swap
         */
        $shift_array=new ArrayCollection( $this->eligiblerShift->findSwapShiftEligible($shift));


        foreach ($swaps as $swap){
            if (!$shift_array->contains($swap->getShift()))
                throw new InvalidArgumentException('shift cannot swap with your shift');
            $swap->setShiftRequest($shiftRequest);
            $swap->setUser($swap->getShift()->getOwnerId());
        }

        $this->manager->persist($shiftRequest);

        $shiftRequestLog=new ShiftRequestLog();
        $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
        $shiftRequestLog->setShiftRequestId($shiftRequest);
        $shiftRequestLog->setMessage($shiftRequest->getMessage());
        $shiftRequestLog->setType($shiftRequest->getStatus());
        $shiftRequestLog->setCreatorId($user);
        $this->manager->persist($shiftRequestLog);





    }

    /**
     * @param ShiftRequest $shiftRequest
     * @param User $user
     */
    public function updateShiftRequestSwap($shiftRequest,$user)
    {
        $status=$shiftRequest->getStatus();
        $accepter=null;
        /**
         * @var UnitOfWork $uow
         */
        $uow = $this->manager->getUnitOfWork();
        $uow->computeChangeSet($this->manager->getClassMetadata(ShiftRequest::class),$shiftRequest);
        $changeSet = $uow->getEntityChangeSet($shiftRequest);
        foreach ($changeSet as $key=>$changes){
            if ($key === 'status' && $changes[0]!== ShiftRequest::SHIFT_STATUS[2])//only approved could be accept or decline
                throw new InvalidArgumentException('you cannot accept this swap in this status');

        }

        //common in accept and decline
        $requesterShift=$shiftRequest->getRequesterShift();
        $requester_id=$shiftRequest->getRequesterId();
        /**
         * @var SwapUserShiftAccept $swapped_shift
         */
        $swapped_shift=$shiftRequest->getSwaps()->getValues()[0];



        $old_swapped=$shiftRequest->getSwaps()->getSnapshot();
        //accept
        if ($shiftRequest->getStatus() === ShiftRequest::SHIFT_STATUS[0]){
            $in_swaps=false;

            //validate user login
            if ($user->getId() !== $swapped_shift->getUser()->getId()){
                throw new InvalidArgumentException('you cannot accept others swap');
            }

            /**
             * @var SwapUserShiftAccept $item
             */
            foreach ($old_swapped as  $item){

              if ($item->getId() === $swapped_shift->getId()){
                  $accepter=$item->getUser();
                  $in_swaps=true;
                  $item->setStatus(true);
                  $requesterShift->setOwnerId($item->getUser());
                  $shift=$item->getShift();
                  $shift->setOwnerId($requester_id);
                  $this->manager->persist($shift);
                  $this->manager->persist($requesterShift);
                  $this->manager->persist($item);
              }else{
                  $shiftRequest->addSwap($item);
              }
            }
            if (!$in_swaps){
                throw new InvalidArgumentException('swapped shift not found in eligible saved swaps');
            }else{
                //create log and history for changed shift
                $shiftRequestLog=new ShiftRequestLog();
                $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
                $shiftRequestLog->setType($shiftRequest->getStatus());
                $shiftRequestLog->setCreatorId($this->security->getUser());
                $shiftRequestLog->setShiftRequestId($shiftRequest);
                $this->manager->persist($shiftRequestLog);
                //send notification to requester
                $user=$shiftRequest->getRequesterId();

                $this->notifier->sendNotification($user,Notifier::createMessage('shift request','you shift request accept by '.$accepter->getFirstName()." ".$accepter->getLastName(),$shiftRequest));

            }
        }



        //decline
        if ($status === ShiftRequest::SHIFT_STATUS[5]){
            $in_swaps=false;
             /**
             * @var SwapUserShiftAccept $item
             */
            //validate user login
            if ($user->getId() !== $swapped_shift->getUser()->getId()){
                throw new InvalidArgumentException('you cannot accept others swap');
            }

            foreach ($old_swapped as  $item){

                if ($item->getId() === $swapped_shift->getId()){
                    $in_swaps=true;
                    $item->setStatus(false);
                    //send notification to requester that his reuest reject by this user
                    $user=$shiftRequest->getRequesterId();
                    $this->notifier->sendNotification($user,
                                                    Notifier::createMessage('shift request','you shift request eject by '.$item->getUser()->getFirstName()." ".$item->getUser()->getLastName(),$shiftRequest),
                                                     $shiftRequest);

                }else{
                    $shiftRequest->addSwap($item);
                }
            }
            if (!$in_swaps){
                throw new InvalidArgumentException('swapped shift not found in eligible saved swaps');
            }
        }


        //cancel
        if ($status === ShiftRequest::SHIFT_STATUS[3]){

            if ($user !== $shiftRequest->getRequesterId()){
                throw new UnauthorizedHttpException('role','you are not permitted to cancel others shift');
            }
            $shiftRequestLog=new ShiftRequestLog();
            $shiftRequestLog->setShiftRequestId($shiftRequest);
            $shiftRequestLog->setCreatorId($user);
            $shiftRequestLog->setRequestDate($this->timezone->generateSystemDate());
            $shiftRequestLog->setType($shiftRequest->getStatus());
            $this->manager->persist($shiftRequestLog);

        }

    }



    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['ShiftRequestPrePost',EventPriorities::PRE_WRITE],
        ];


    }
}
