<?php


namespace App\Service;


use App\Entity\AttendanceSetting;
use App\Entity\Shift;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class AttendanceSettingService
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(Security $security, BusinessFinder $finder,EntityManagerInterface $manager)
    {
        $this->security = $security;
        $this->finder = $finder;
        $this->manager = $manager;
    }

    /**
     * @return AttendanceSetting|null
     */
    public function getAttendanceSetting()
    {
        $business=$this->finder->getCurrentUserBusiness();
        $repo_setting=$this->manager->getRepository(AttendanceSetting::class);
        $attendanceSetting=$repo_setting->findOneBy(['business'=>$business]);
        return $attendanceSetting;

    }

    /**
     * @param Shift $shift
     * @param $lat
     * @param $lang
     * @param $schedule
     * @return float|int
     */
    public function getDistanceFromShift($shift,$lat,$lang,$schedule)
    {
        if (!empty($shift) && !empty($shift->getJobSitesId())){

            $src_lat=$shift->getJobSitesId()->getLat();
            $src_lang=$shift->getJobSitesId()->getLang();

        }else{
            $src_lat=$schedule->getLat();
            $src_lang=$schedule->getLang();
        }

         $distance=$this->distance($src_lat, $src_lang, $lat, $lang, "K") ;
         return $distance*1000;
    }


    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }


}
