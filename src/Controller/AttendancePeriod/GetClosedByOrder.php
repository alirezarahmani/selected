<?php


namespace App\Controller\AttendancePeriod;


use ApiPlatform\Core\Exception\InvalidValueException;
use App\Entity\AttendancePeriod;
use App\Entity\Business;
use App\Service\BusinessFinder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class GetClosedByOrder
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;

    public function __construct(EntityManagerInterface $manager,BusinessFinder $finder)
    {
        $this->manager = $manager;
        $this->finder = $finder;
    }

    public function __invoke(Request $request)
    {
        $closed=$request->query->get('closed');
        /**
         * @var Business $business
         */
        $business=$this->finder->getUserBusiness();
        $conn=$this->manager->getConnection();
        $sql_false='SELECT * FROM `attendance_period` WHERE `closed`=false AND `business_id`='.$business.'  ORDER BY id Asc LIMIT 1';
        $sql_true="SELECT * FROM `attendance_period` WHERE `closed`=true AND `business_id`=".$business."  ORDER BY id Desc LIMIT 1";

        if ($closed==='true'){

            $stm=$conn->prepare($sql_true);


        }else{
            $stm=$conn->prepare($sql_false);
        }
        $result=$stm->execute();
        if (!$request){
            throw new InvalidValueException('bad query '.$stm);
        }
        $periods=$stm->fetchAll();

        return $periods;


    }

}
