<?php


namespace App\Controller\Availability;


use App\Entity\Availability;
use App\Entity\Business;
use App\Entity\User;
use App\Service\AvailabilityService;
use App\Service\BusinessFinder;
use App\Service\Notifier;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;


class AddAvailability
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
     * @var User
     */
    private $user;
    /**
     * @var AvailabilityService
     */
    private $availabilityService;
    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(Timezone $timezone,
                                BusinessFinder $finder,
                                Security $security,
                                EntityManagerInterface $manager,
                                AvailabilityService $availabilityService,
                                Notifier $notifier)
    {

        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->security = $security;
        $this->manager = $manager;
        $this->availabilityService = $availabilityService;
        $this->notifier = $notifier;
    }

    /**
     * @param Availability $data
     * @return Availability|null|JsonResponse
     */
    public function __invoke($data)
    {
        $arr = [];
        /**
         * @var Business $business
         */
        $business=$this->finder->getCurrentUserBusiness();
        /**
         * @var Availability $availability
         */
        $data->setBusinessId($business);
        //transform_dates

        $data->setStartTime($this->timezone->transformUserDateToAppTimezone($data->getStartTime()));
        $data->setEndTime($this->timezone->transformUserDateToAppTimezone($data->getEndTime()));
        //end availability setter

        $this->availabilityService->findConflictedAvailabilityWithShift($data);

        //set repeated availability
        if ($data->getRepeated()) {
            $data->setEndReapetedTime($this->timezone->transformUserDateToAppTimezone($data->getEndReapetedTime()));
            $end_repeated_date = $data->getEndReapetedTime();
            //availability and parent are same
            try {

                $parent = $this->availabilityService->generateRepeatedAvaialability($data, $end_repeated_date);
                $this->manager->flush();
                if ($parent!==null){

                    return $parent;

                }else{
                    return new JsonResponse("done ,but no day found",200);
                }

            } catch (\Exception $e) {
                throw new HttpException(400,$e->getMessage());
            }

        } else {
            $data->setEndReapetedTime(null);
            $data->setDays(null);
            $this->manager->persist($data);
            $this->manager->flush();

            return $data;
        }

    }



}
