<?php


namespace App\Controller\Users;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Request;

class ScheduledEmployee
{
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
     * @var IriConverterInterface
     */
    private $iriConverter;



    public function __construct(EntityManagerInterface $manager, Timezone $timezone, BusinessFinder $finder, IriConverterInterface $iriConverter)
    {
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->finder = $finder;
        $this->iriConverter = $iriConverter;
    }


    public function __invoke(Request $request)
    {
        $request_content=json_decode($request->getContent(),true);
        $start=$this->timezone->transformUserDateToAppTimezone($request_content['start_date']);
        $end=$this->timezone->transformUserDateToAppTimezone($request_content['end_date']);


        $business=$this->finder->getCurrentUserBusiness();

        try {
            $start_date = (new \DateTime($start))->format('Y-m-d H:i');
            $end_date = (new \DateTime($end))->format('Y-m-d H:i');
        } catch (\Exception $e) {
            throw new \HttpException($e->getMessage());
        }
        $start_date=$this->timezone->transformUserDateToAppTimezone($start_date);
        $end_date=$this->timezone->transformUserDateToAppTimezone($end_date);

        $query_builder=$this->manager->createQueryBuilder();
        $user_ids=array();
        $owner_Ids=$query_builder
            ->select('u')
            ->distinct()
            ->from(Shift::class,'s')
            ->leftJoin(User::class,'u','WITH','s.ownerId = u')
            ->join('u.userBusinessRoles','ubr')
            ->where('ubr.business ='.$this->finder->getUserBusiness())
            ->andWhere($query_builder->expr()->between('s.startTime',"'".$start_date."'","'".$end_date."'"))
            ->orWhere($query_builder->expr()->between('s.endTime',"'".$start_date."'","'".$end_date."'"))
            ->orWhere($query_builder->expr()->gt("'".$start_date."'",'s.startTime').' AND '.$query_builder->expr()->lt("'".$end_date."'",'s.endTime'))
            ->orWhere($query_builder->expr()->eq("'".$start_date."'",'s.startTime').' AND '.$query_builder->expr()->eq("'".$end_date."'",'s.endTime'))

            ->getQuery()->execute();
        return $owner_Ids;
    }

}
