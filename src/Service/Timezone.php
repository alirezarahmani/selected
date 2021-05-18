<?php


namespace App\Service;


use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;

class Timezone
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var Security
     */
    private $security;

    public function __construct(ParameterBagInterface $parameterBag,BusinessFinder $businessFinder,Security $security)
    {
        $this->parameterBag = $parameterBag;
        $this->businessFinder = $businessFinder;
        $this->security = $security;
    }

    public function getDefaultTimeZone(){
        return $this->parameterBag->get('system_default_timezone');

    }

    public function getDefaultTimeFormat()
    {
      return  $this->parameterBag->get('system_default_time_format');

    }

    /**
     * @param User $user
     * @return mixed|string|null
     */
    public function getUserTimeZone($user)
    {
        $timezone=null;
        //first priority is user custom timezone
        if (!isset($user)){//when user is not login e.g login public terminal
            return $this->getDefaultTimeZone();
        }


        if ($user->getUseCustomTimezone()){
            if (in_array($user->getTimezone(),\DateTimeZone::listIdentifiers()))
                return $user->getTimeZone();

        }
        $business=$this->businessFinder->getCurrentUserBusiness();

        if ($business!==null){
            return $business->getTimeZone();

        }




        return $this->getDefaultTimeZone();

    }

    public function transformUserDateToAppTimezone($date,$format=null)
    {
        /**
         * @var User $user
         */
        $user=$this->security->getUser();

        $input = (new \DateTime($date))->format($this->getDefaultTimeFormat());
        $date =date_create($input, timezone_open($this->getUserTimeZone($user)));
        date_timezone_set($date,timezone_open($this->getDefaultTimeZone()));


        $time= empty($format)?$date->format($this->getDefaultTimeFormat()):$date->format($format);

        return $time;

    }

    public function transformSystemDateToUser($date,$user=null){
        /**
         * @var User $user
         */

        if (!isset($user)){
            $user=$this->security->getUser();
        }

        $input = (new \DateTime($date))->format($this->getDefaultTimeFormat());
        $date =date_create($input, timezone_open($this->getDefaultTimeZone()));
        date_timezone_set($date,timezone_open($this->getUserTimeZone($user)));

        $time= $date->format($this->getDefaultTimeFormat());
        return $time;
    }

    /**
     * @param string $date
     * @return string
     * @throws \Exception
     */
    public function generateSystemDate(string $date='now')
    {
        $date = new \DateTime($date, new \DateTimeZone($this->getDefaultTimeZone()));
        $time= $date->format($this->getDefaultTimeFormat());
        return $time;
    }

    public function hasConflict($first_start,$first_end,$second_start,$second_end){
        $fs=strtotime($first_start);
        $fe=strtotime($first_end);
        $ss=strtotime($second_start);
        $se=strtotime($second_end);

        if ($fs>$ss && $fs<$se)
            return true;
        if ($fe> $ss && $fe<$se)
            return true;
        if ($fs<$ss && $fe>$se)
            return true;
        if ($ss=== $fs && $se === $fe){
            return true;
        }
        return false;
    }

}
