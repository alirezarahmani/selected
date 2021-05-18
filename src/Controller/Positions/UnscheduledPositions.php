<?php


namespace App\Controller\Positions;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Position;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\BusinessFinder;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use function Doctrine\ORM\QueryBuilder;

class UnscheduledPositions
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(EntityManagerInterface $manager,Timezone $timezone,BusinessFinder $finder,IriConverterInterface $iriConverter)
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
        /**
         * @var Schedule $schedule
         */
        $schedule=$this->iriConverter->getItemFromIri($request_content['schedule']);

        try {
            $start_date = (new \DateTime($start))->format('Y-m-d H:i');
            $end_date = (new \DateTime($end))->format('Y-m-d H:i');
        } catch (\Exception $e) {
            throw new \HttpException($e->getMessage());
        }
        $start_date=$this->timezone->transformUserDateToAppTimezone($start_date);
        $end_date=$this->timezone->transformUserDateToAppTimezone($end_date);

        $query_builder=$this->manager->createQueryBuilder();
        $poses=array();
        $poses=$query_builder
            ->select('p.id')
            ->distinct()
            ->from(Shift::class,'s')
            ->leftJoin(Position::class,'p','WITH','s.positionId = p')
            ->where($query_builder->expr()->eq('s.scheduleId',$schedule->getId()))
            ->where($query_builder->expr()->between('s.startTime',"'".$start_date."'","'".$end_date."'"))
            ->orWhere($query_builder->expr()->between('s.endTime',"'".$start_date."'","'".$end_date."'"))
            ->orWhere($query_builder->expr()->gt("'".$start_date."'",'s.startTime').' AND '.$query_builder->expr()->lt("'".$end_date."'",'s.endTime'))
            ->orWhere($query_builder->expr()->eq("'".$start_date."'",'s.startTime').' AND '.$query_builder->expr()->eq("'".$end_date."'",'s.endTime'))

            ->getQuery()->execute();

        foreach ($poses as $key=>$pos){
            $pos_ids[]=$pos['id'];
        }
        $query_builder=$this->manager->createQueryBuilder();
        $query_builder
            ->select('p')
            ->from(Position::class,'p')

            ->where($query_builder->expr()->eq('p.business_id',$this->finder->getUserBusiness()));
        if (count($poses)>0)
            $query_builder->andWhere($query_builder->expr()->notIn('p',$pos_ids));

        $unscheduled=$query_builder->getQuery()->execute();
        return $unscheduled;
    }

}
