<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\AttendanceTimes;
use App\Entity\Availability;
use App\Entity\Business;
use App\Entity\Shift;
use App\Entity\ShiftHistory;
use App\Entity\ShiftRequest;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\EligiblerShift;
use App\Service\Notifier;
use App\Service\Timezone;
use DateInterval;
use DatePeriod;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function Doctrine\ORM\QueryBuilder;

class ShiftWriteSubscriber implements EventSubscriberInterface
{
    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var NormalizerInterface
     */
    private $normalizer;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var EligiblerShift
     */
    private $eligiblerShift;
    /**
     * @var Notifier
     */
    private $notifier;


    public function __construct(BusinessFinder $businessFinder,
                                EntityManagerInterface $manager,
                                NormalizerInterface $normalizer,
                                Security $security,
                                Timezone $timezone,
                                Notifier $notifier,
                                EligiblerShift $eligiblerShift,
                                RequestStack $requestStack)
    {
        $this->businessFinder = $businessFinder;
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->normalizer = $normalizer;
        $this->security = $security;
        $this->requestStack = $requestStack;

        $this->eligiblerShift = $eligiblerShift;
        $this->notifier = $notifier;
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => [
                ['shiftPreWrite',EventPriorities::PRE_WRITE],
            ]
        ];
    }

    /**
     * @param $start
     * @param $end
     * @param Shift $shift
     * @return false|float
     */
    public function calculate_shift_scheduled($start, $end,$shift)
    {
        $date1 = strtotime($start);
        $date2 = strtotime($end);
        $diff_seconds = abs($date2 - $date1);
        $unpaid=$shift->getUnpaidBreak();
        return floor($diff_seconds / 60)-$unpaid;
    }

    /**
     * @param Shift $shift
     * @param Request $request
     * @throws \Exception
     */
    private function shiftPreDelete($shift,$request)
    {
        $req_content=json_decode($request->getContent(),true);
        $chain=$req_content['chain'];

        if(count($shift->getAttendanceTimes()->getValues())>0){
            throw new InvalidArgumentException('you cannot delete shift that users clock in them');
        };

        if (!is_null($shift->getParentId()) && $shift->getId() == $shift->getParentId()->getId()){

            $siblings=$this->manager->getRepository(Shift::class)->findBy(['parentId'=>$shift->getParentId()]);
            foreach ($siblings as $shift){
               $shift->setRepeated(false);
               $shift->setEndRepeatTime(null);
               $shift->setRepeatPeriod(null);
               $this->manager->persist($shift);
            }
        }
        if (!empty($chain) && $chain && $shift->getRepeated()){
            $siblings=$this->manager->getRepository(Shift::class)->findBy(['parentId'=>$shift->getParentId()]);
            foreach ($siblings as $shift){
                $shift_end_repeated_date=new \DateTime($shift->getEndRepeatTime());
                $sibling_start=new \DateTime($shift->getStartTime());
                $today=new \DateTime();
                $diff= $sibling_start->getTimestamp() - $shift_end_repeated_date->getTimestamp();
                $is_future= $sibling_start->getTimestamp() - $today->getTimestamp();
                if ($is_future>0)
                    $this->manager->remove($shift);
            }

        }
    }

    /**
     * @param Shift $shift
     * @param Request $request
     * @throws \Exception
     */
    private function shiftPrePut($shift,$request)
    {
        if(count($shift->getAttendanceTimes()->getValues())>0){//prevent delete or update shift has attendance
            throw new InvalidArgumentException('you cannot update shift that users clock in them');
        };

        //set scheduled again on each update
        $diff_shift=$this->calculate_shift_scheduled($shift->getStartTime(),$shift->getEndTime(),$shift);
        $shift->setScheduled($diff_shift);
        $shift->removeAllConflictedAvailability();//remove all




        $repeated=$shift->getRepeated();
        $sec_user=$this->security->getUser();
        $shift->setInformed(false);



        $this->manager->persist($shift);
//
        //compute shifts change
        /**
         * @var UnitOfWork $uow
         */
        $uow = $this->manager->getUnitOfWork();
        $uow->computeChangeSet($this->manager->getClassMetadata(Shift::class),$shift);
        $changeSet = $uow->getEntityChangeSet($shift);

        $shiftHistory_changes=' ';
        foreach ($changeSet as $key=>$changes){
            $shiftHistory_changes.=" ".$key.' changes from'.$changes[0].' to '.$changes[1];


        }

        //if drop shift in open shifts then set eligible programmatically
        if (empty($shift->getOwnerId())){
            $shift->removeAllEligibilty();
            $shift_eligible=$this->eligiblerShift->findOpenShiftEligible($shift->getStartTime(),$shift->getEndTime(),$shift->getScheduleId(),$shift->getPositionId());
            foreach ($shift_eligible as $emp){
                $shift->addEligibleOpenShiftUser($emp);
            }
        }
        //end

        //implement changes for repeated shift
        if ($shift->getChain() && $repeated){

            $siblings=$this->manager->getRepository(Shift::class)->findBy(['parentId'=>$shift->getParentId()]);
            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            //first change on existance sibling~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            /**
             * @var Shift $item
             */
            foreach ($siblings as $item){

                if ($item->getClosed()){
                    continue;
                }
                $item->setInformed(false);
                $item->removeAllConflictedAvailability();//remove all conflict
                foreach ($changeSet as $key=>$changes){
                    //if end repeated time increase should new shift generate if decrease should remove redundants
                    if ($key === 'endRepeatTime'){
                            $shift_end_repeated_date=new \DateTime($shift->getEndRepeatTime());
                            $sibling_start=new \DateTime($item->getStartTime());
                            $today=new \DateTime();
                            $diff= $sibling_start->getTimestamp() - $shift_end_repeated_date->getTimestamp();
                            $is_future= $sibling_start->getTimestamp() - $today->getTimestamp();
                            //sibling is after end and not expired
                            if ($diff>0 && $is_future>0){
                                $this->manager->remove($item);
                            }else{
                                $item->setEndRepeatTime($shift->getEndRepeatTime());
                            }


                    }
                    elseif ($key === 'endTime' ){
                        $end_shift=new \DateTime($shift->getEndTime());
                        $diff=$end_shift->diff(new \DateTime($shift->getStartTime()));
                        $minutes=($diff->days * 24 * 60) +
                        ($diff->h * 60) + $diff->i;

                        $start_item = new \DateTime($item->getStartTime());
                        $new_end_time=$start_item ->add(new DateInterval('PT' . $minutes . 'M'));
                        $item->setEndTime($new_end_time->format($this->timezone->getDefaultTimeFormat()));

                        //set scheduled again on each sibling
                        $diff_item=$this->calculate_shift_scheduled($item->getStartTime(),$item->getEndTime(),$item);
                        $item->setScheduled($diff_item);

                        $this->deleteRelatedShiftRequest($item);


                    }
                    elseif ($key === 'startTime'){
                        $end_shift=new \DateTime($shift->getEndTime());
                        $diff=$end_shift->diff(new \DateTime($shift->getStartTime()));
                        $minutes=($diff->days * 24 * 60) +
                            ($diff->h * 60) + $diff->i;

                        $shift_end = new \DateTime($item->getEndTime());
                        $new_end_time=$shift_end ->sub(new DateInterval('PT' . $minutes . 'M'));
                        $item->setStartTime($new_end_time->format($this->timezone->getDefaultTimeFormat()));
                        //set scheduled again on each sibling
                        $diff_item=$this->calculate_shift_scheduled($item->getStartTime(),$item->getEndTime(),$item);
                        $item->setScheduled($diff_item);
                        $this->deleteRelatedShiftRequest($item);

                    }
                    else{
                        if ($propertyAccessor->isWritable($item,$key) ){
                            $propertyAccessor->setValue($item,$key,$propertyAccessor->getValue($shift,$key));
                        }

                    }
                    $this->getConflictedAvailability($item);
                    $this->manager->persist($item);
                }
               //create shift history foreach siblings~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

                $this->createShiftHistory($item,$shiftHistory_changes,$sec_user);

            }
            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


            //if end repeated time increase should new shifts generate and also if start and endtime change delete shifts request related~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            foreach ($changeSet as $k=>$values){
                if($k==='endRepeatTime' && strtotime($values[0])<strtotime($values[1])){
                    try {
                        // Variable that store the date interval
                        $interval = new DateInterval('P'.$shift->getRepeatPeriod().'D');
                        $end_repeat_time=new \DateTime($shift->getEndRepeatTime());
                        $shift_start_time=new \DateTime($shift->getStartTime());
                        $shift_end_time=new \DateTime($shift->getEndTime());
                        $period_start = iterator_to_array(new DatePeriod($shift_start_time, $interval, $end_repeat_time));
                        $period_end = new DatePeriod($shift_end_time, $interval, $end_repeat_time);
                        $array_shift=array();

                        // Use loop to generate shift an loop should be on the end to be sure its in the period
                        if (count(iterator_to_array($period_end))>0) {
                            /**
                             * @var \DateTime $date
                             */
                            foreach ($period_end as $key => $date) {
                                //for date after last repeated time should shift generate
                                if ($date->getTimestamp() >strtotime($values[0])){
                                    $array_shift[$key] = clone $shift;
                                    /**
                                     * @var Shift $new
                                     */
                                    $new = $array_shift[$key];
                                    $new->setStartTime($period_start[$key]->format($this->timezone->getDefaultTimeFormat()));
                                    $new->setEndTime($date->format($this->timezone->getDefaultTimeFormat()));
                                    $new->setParentId($shift->getParentId());
                                    $diff_new=$this->calculate_shift_scheduled($new->getStartTime(),$new->getEndTime(),$new);
                                    $new->setScheduled($diff_new);
                                    $this->manager->persist($new);


                                    //create shift history for generated shift~~~~~~~~~~~~~~~~~~~~~~
                                    $shiftHistory=new ShiftHistory();
                                    $shiftHistory->setDate($this->timezone->generateSystemDate());
                                    $shiftHistory->setType(ShiftHistory::SHIFT_CREATE);
                                    $shiftHistory->setShiftId($shift);
                                    $shiftHistory->setUserId($sec_user);
                                    $this->manager->persist($shiftHistory);
                                }


                            }
                        }
                    } catch (\Exception $e) {
                        throw new InvalidArgumentException($e->getMessage());
                    }
                }

            }
//            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            //recompute availaility conflict
            $this->getConflictedAvailability($shift);
        }else{
            //if just change repeted field

            if (array_key_exists('repeated',$changeSet) && !$repeated){
                $siblings=$this->manager->getRepository(Shift::class)->findBy(['parentId'=>$shift->getParentId()]);
                if ($shift->getChain()){
                    /**
                     * @var Shift $sh
                     */
                    foreach ($siblings as $sh){
                        $sh->setRepeated(false);
                        $sh->setEndRepeatTime(null);
                        $sh->setRepeatPeriod(null);
                        $sh->setParentId(null);
//                        $sh->removeAllConflictedAvailability();//no need to change conflict because its time same
                        $this->manager->persist($sh);
                    }
                }
                if ($shift->getParentId()===$shift->getId()) {
                    /**
                     * @var Shift $sh
                     */
                    foreach ($siblings as $sh) {
                        $sh->setRepeated(false);
                        $sh->setEndRepeatTime(null);
                        $sh->setRepeatPeriod(null);
                        $sh->setParentId(null);
//                        $sh->removeAllConflictedAvailability();//no need to change conflict because its time same

                        $this->manager->persist($sh);
                    }
                }
                $shift->setRepeated(false);
                $shift->setEndRepeatTime(null);
                $shift->setRepeatPeriod(null);
                $shift->setParentId(null);
                $this->getConflictedAvailability($shift);
                $meta = $this->manager->getClassMetadata(get_class($shift));
                $uow->recomputeSingleEntityChangeSet($meta, $shift);

            }
            $this->createShiftHistory($shift,$shiftHistory_changes,$sec_user);
            $this->deleteRelatedShiftRequest($shift);

        }
        $meta = $this->manager->getClassMetadata(get_class($shift));
        $uow->recomputeSingleEntityChangeSet($meta, $shift);
    }

    /**
     * @param Shift $shift
     * @param Request $request
     * @throws \Exception
     */
    private function shiftPrePost($shift,$request)
    {

        if ($request->attributes->get('_api_collection_operation_name')!== 'post')
            return;

        //set scheduled  on post
        $diff_shift=$this->calculate_shift_scheduled($shift->getStartTime(),$shift->getEndTime(),$shift);
        $shift->setScheduled($diff_shift);


        $repeated=$shift->getRepeated();
        /**
         * @var User $sec_user
         */
        $sec_user=$this->security->getUser();
//       //@todo:attention never and never uncomment following lines to prevent fucked project
        //just in post both stat time and end time sent from user so they ae in his timezone
         //when you set time for 8:am your means is 8:00 you
         //setShift Startime and endtime without timezone these lines commented because shift transformation date on normalizing
//        $shift->setStartTime($this->timezone->transformUserDateToAppTimezone($shift->getStartTime()));
//        $shift->setEndTime($this->timezone->transformUserDateToAppTimezone($shift->getEndTime()));


        //businesss setting
        if (!$this->businessFinder->getCurrentUserBusiness()->getShiftConfirmation()){
            $shift->setConfirm(true);
        }

        //setEligibleEmployee
        if (empty($shift->getOwnerId()) && count($shift->getEligibleOpenShiftUser())==0){
            $eligibles=$this->eligiblerShift->findOpenShiftEligible($shift->getStartTime(),$shift->getEndTime(),$shift->getScheduleId(),$shift->getPositionId());
            if (count($eligibles)>0)
                foreach ($eligibles as $user){
                    $shift->addEligibleOpenShiftUser($user);
                }
        }

        //If shift is repeated child shift should be generate
        if ($repeated){
            $shift->setParentId($shift);
            $conflicted=$this->getConflictedAvailability($shift);

            $this->manager->persist($shift);
            try {
                // Variable that store the date interval
                $interval = new DateInterval('P'.$shift->getRepeatPeriod().'D');
                $end_repeat_time=new \DateTime($shift->getEndRepeatTime());
                $shift_start_time=new \DateTime($shift->getStartTime());
                $shift_end_time=new \DateTime($shift->getEndTime());
                $period_start = iterator_to_array(new DatePeriod($shift_start_time, $interval, $end_repeat_time));
                $period_end = new DatePeriod($shift_end_time, $interval, $end_repeat_time);
                $array_shift=array();

                // Use loop to generate shift an loop should be on the end to be sure its in the period
                if (count(iterator_to_array($period_end))>0) {
                    foreach ($period_end as $key => $date) {
                        if ($key > 0){
                            $array_shift[$key] = clone $shift;
                            /**
                             * @var Shift $new
                             */
                            $new = $array_shift[$key];
                            $new->setStartTime($period_start[$key]->format($this->timezone->getDefaultTimeFormat()));
                            $new->setEndTime($date->format($this->timezone->getDefaultTimeFormat()));
                            $new->setParentId($shift);
                            $this->manager->persist($new);
                        }


                    }
                }

            } catch (\Exception $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }else{
            $conflicted=$this->getConflictedAvailability($shift);
            $shift->setEndRepeatTime(null);
            $shift->setRepeatPeriod(null);
            $this->manager->persist($shift);
        }
        //end generate childs

        //generate History
        if ($repeated){
            //create history for childs
            foreach ($array_shift as $shift_item){
                $shiftHistory=new ShiftHistory();
                $shiftHistory->setDate($this->timezone->generateSystemDate());
                $shiftHistory->setType(ShiftHistory::SHIFT_CREATE);
                $shiftHistory->setShiftId($shift_item);
                $shiftHistory->setUserId($sec_user);
                $this->manager->persist($shiftHistory);
            }
        }


        $shiftHistory=new ShiftHistory();
        $shiftHistory->setDate($this->timezone->generateSystemDate());
        $shiftHistory->setType(ShiftHistory::SHIFT_CREATE);
        $shiftHistory->setShiftId($shift);
        $shiftHistory->setUserId($sec_user);
        $this->manager->persist($shiftHistory);


    }

    /**
     * @param Shift $shift
     */
    private function validateShift($shift)
    {
        $business_id=$this->businessFinder->getUserBusiness();
        $positions=$shift->getPositionId();
        $schedules=$shift->getScheduleId();
        $job_sites=$shift->getJobSitesId();
        $user=$shift->getOwnerId();
        $repeated=$shift->getRepeated();
        //check valid entity for shift be sets as schedule ,position ,jobSite ,owner
        $business=$this->manager->getRepository(Business::class)->findOneBy(["id"=>$business_id]);
        if (!$business->getSchedules()->contains($schedules))
            throw new InvalidArgumentException('business not contain schedule');
        if (!empty($shift->getPositionId()) && !$business->getPositions()->contains($positions))
            throw new InvalidArgumentException('business not contain positions');
        if (!empty($shift->getJobSitesId()) && !$business->getJobSites()->contains($job_sites))
            throw new InvalidArgumentException('business not contain jobSites');
        if (!empty($user) && $this->manager->getRepository(UserBusinessRole::class)->findOneBy(['user'=>$user->getId(),'business'=>$business_id])===null)
            throw new InvalidArgumentException('business not contain owner');
        if ($repeated){
            if (empty($shift->getRepeatPeriod())){
                throw new InvalidArgumentException('if repeated true repeat period  should not empty');}
            if ($shift->getEndRepeatTime() ==null){
                throw new InvalidArgumentException('if repeated true end repeat time should not empty');
            }
        }



    }

    public function createShiftHistory($shift,$changedProperty,$sec_user)
    {
        $shiftHistory=new ShiftHistory();
        $shiftHistory->setDate($this->timezone->generateSystemDate());
        $shiftHistory->setType(ShiftHistory::SHIFT_UPDATE);
        $shiftHistory->setShiftId($shift);
        $shiftHistory->setUserId($sec_user);
        $shiftHistory->setChangedProperty($changedProperty);
        $this->manager->persist($shiftHistory);

    }

    /**
     * @param Shift $shift
     */
    public function deleteRelatedShiftRequest($shift)
    {
        $qb=$this->manager->createQueryBuilder();
        $qb->select('sr')
            ->from(ShiftRequest::class,'sr')
            ->leftJoin('sr.swaps','sw')
            ->where($qb->expr()->orX($qb->expr()->eq('sr.requesterShift',$shift->getId()), $qb->expr()->eq('sw.shift',$shift->getId())))
            ->andWhere($qb->expr()->neq('sr.status',"'accept'"));

        $shift_requests=$qb->getQuery()->getResult();
        foreach ($shift_requests as $req){
            try{
                $this->manager->remove($req);
            }catch(\Exception $exception){
                dd($exception->getMessage());
            }
        }
    }


    public function shiftPreWrite(ViewEvent $event)
    {
        $shift = $event->getControllerResult();
        $request=$event->getRequest();
        $method = $event->getRequest()->getMethod();
        $params=json_decode($request->getContent(),true);
        if (!$shift instanceof  Shift)
            return;

        if ($shift instanceof Shift) {
            //validate shift inputs
            $this->validateShift($shift);
            if ($method === Request::METHOD_POST ||
                $method === Request::METHOD_DELETE ){
                $this->notifier->sendAccountManagerNotification($shift->getScheduleId(),Notifier::SCHEDULE_UPDATE);

            }

            if ($method === Request::METHOD_POST){
                if (!empty($shift->getEndRepeatTime())){
                    $shift->setEndRepeatTime($this->timezone->transformUserDateToAppTimezone($shift->getEndRepeatTime()));
                }
                //this should be after compare,  to correct compare before and after
                if (array_key_exists('startTime',$params)){
                    $shift->setStartTime($this->timezone->transformUserDateToAppTimezone($params['startTime']));

                }
                if (array_key_exists('endTime',$params)){
                    $shift->setEndTime($this->timezone->transformUserDateToAppTimezone($params['endTime']));

                }
                $this->shiftPrePost($shift,$request);
            }

            if ($method === Request::METHOD_PUT){



                if (!empty($shift->getEndRepeatTime())){
                    $shift->setEndRepeatTime($this->timezone->transformUserDateToAppTimezone($shift->getEndRepeatTime()));
                }

                //this should be after compare,  to correct compare before and after
                //if user send this params should be edit else edit then change time
                if (array_key_exists('startTime',$params)){
                    $shift->setStartTime($this->timezone->transformUserDateToAppTimezone($params['startTime']));

                }
                if (array_key_exists('endTime',$params)){
                    $shift->setEndTime($this->timezone->transformUserDateToAppTimezone($params['endTime']));

                }
                $this->shiftPrePut($shift,$request);
            }

            if ($method === Request::METHOD_DELETE){
                $this->shiftPreDelete($shift,$request);
            }

        }

    }

    /**
     * @param Shift $shift
     */
    public function getConflictedAvailability($shift)
    {
       $owner=$shift->getOwnerId();
        if (!isset($owner ) ){
          return;
        }
        $start_time=$shift->getStartTime();
        $end_time=$shift->getEndTime();

        $queryBuilder=$this->manager->createQueryBuilder();;

        $queryBuilder->select('av')
            ->from(Availability::class,'av')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte("'" . $start_time . "'", 'av.startTime')
                ,$queryBuilder->expr()->lt("'" . $start_time . "'", 'av.endTime')))
            ->orWhere($queryBuilder->expr()->andX(
                $queryBuilder->expr()->lte("'" . $end_time . "'", 'av.endTime')
                ,$queryBuilder->expr()->gt("'" . $end_time . "'", 'av.startTime')))
            ->orWhere($queryBuilder->expr()->andX(
                $queryBuilder->expr()->lte("'" . $start_time . "'", 'av.startTime'),
                $queryBuilder->expr()->gte("'" . $end_time . "'", 'av.startTime')
            ))
            ->andWhere('av.user =' . $shift->getOwnerId()->getId());
        $conflicted=$queryBuilder->getQuery()->execute();

        if (count($conflicted)>0){
            /**
             * @var $avail Availability
             */
            foreach ($conflicted as $avail){
                $shift->addConflictAvailability($avail);
            }
        }

    }





}
