<?php

//logic base wheniwork

//if reapeted beame false and chain false just that one shift became seprate


namespace App\Controller\Availability;


use App\Entity\AttendanceTimesLog;
use App\Entity\Availability;
use App\Service\AvailabilityService;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EditAvailability
{
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var ObjectNormalizer
     */
    private $normalizer;
    /**
     * @var AvailabilityService
     */
    private $availabilityService;

    public function __construct(Timezone $timezone,
                                BusinessFinder $finder,
                                Security $security,
                                EntityManagerInterface $manager,
                                AvailabilityService $availabilityService,
                                ObjectNormalizer $normalizer)
    {
        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->security = $security;
        $this->manager = $manager;
        $this->normalizer = $normalizer;
        $this->availabilityService = $availabilityService;
    }

    /**
     * @param Availability $data
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function __invoke($data,Request $request)
    {
        //calculate changeset
            $uow = $this->manager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeSet = $uow->getEntityChangeSet($data);

        //transform_dates

        $data->setStartTime($this->timezone->transformUserDateToAppTimezone($data->getStartTime()));
        $data->setEndTime($this->timezone->transformUserDateToAppTimezone($data->getEndTime()));
        if ($data->getRepeated()){
            $data->setEndReapetedTime($this->timezone->transformUserDateToAppTimezone($data->getEndReapetedTime()));
        }

        //REMOVE ALL CONFLICTED TABLE WITH SHIFT AND RECALCULATED
        $data->removeAllConflictedShift();


        $sibling = $this->manager->getRepository(Availability::class)->findBy(['parentAvailable' => $data->getParentAvailabilityId()]);
        //chain false
        //if repeated became false && chain true all  child be deleted base wheniwork
        if ( ($data->getChain() && !$data->getRepeated() )){
            if ( $data->getParentAvailabilityId()===$data){//if repeated became false && chain false and parent prevent base wheniwork
                throw new HttpException("you should update all ,this availability has conflict with others");
            }
            /**
             * @var Availability $item
             */
            foreach ($sibling as $item){//attention that data is inside siblings also
                $avail_start = strtotime($item->getStartTime());
                $now = strtotime($this->timezone->generateSystemDate());
                if ($avail_start > $now && $item->getParentAvailabilityId()!==$item) {
                    $item->removeAllConflictedShift();
                    $this->manager->remove($item);

                }
            }
        }
        else if(!$data->getChain() && !$data->getRepeated()){
            {
                $data->setParentAvailabilityId(null);
                $data->setDays(null);
                $data->setEndReapetedTime(null);


            }
            $this->manager->persist($data);

        }

        else if (($data->getRepeated() && $data->getChain())|| ($data->getRepeated() &&!$changeSet['repeated'][0])){
            foreach ($sibling as $avail){
                $avail_start = strtotime($avail->getStartTime());
                $now = strtotime($this->timezone->generateSystemDate());
                if ($avail_start > $now)
                    $this->manager->remove($avail);
            }
            $parent=$data->getParentAvailabilityId();
            $start=isset($parent)?$parent->getStartTime():$data->getStartTime();//in case $data now first time became repeated
            $this->availabilityService->generateRepeatedAvaialability($data,$data->getEndReapetedTime(),$start,true);
        }
        $this->manager->flush();
        return new JsonResponse(['code'=>200,'message'=>'update successfully']);
    }



}
