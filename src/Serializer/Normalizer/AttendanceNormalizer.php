<?php

namespace App\Serializer\Normalizer;


use App\Entity\AttendanceTimes;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;


class AttendanceNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ATTENDANCE_NORMALIZER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
    /**
     * @var Timezone
     */
    private $timezone;


    public function __construct(Timezone $timezone)
    {

        $this->timezone = $timezone;
    }

    public function normalize($object, $format = null, array $context = [])
    {

        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);
        if(array_key_exists('startTime',$data) && !is_null($data['startTime'])){
            $startTime=$data['startTime'];
            $start_transformed=$this->timezone->transformSystemDateToUser($startTime);
            $data['startTime']=$start_transformed;

        }
        if(array_key_exists('endTime',$data) && !is_null($data['endTime'])){
            $endTime=$data['endTime'];
            $end_transformed=$this->timezone->transformSystemDateToUser($endTime);
            $data['endTime']=$end_transformed;

        }

        if (array_key_exists('worked',$data)){
            $worked=$data['worked'];
            $hours_worked = floor($worked / 60);
            $minutes_worked = floor(($worked ) % 60);
            $data['worked']=$hours_worked .":". $minutes_worked;
        }
        if (array_key_exists('scheduled',$data)){
            $scheduled=$data['scheduled'];
            $hours_scheduled = floor($scheduled / 60);
            $minutes_scheduled = floor($scheduled % 60);
            $data['scheduled']=$hours_scheduled .":". $minutes_scheduled;
        }
        if (array_key_exists('diff',$data)){
            $diff=abs($data['diff']);
            $hours_diff = floor($diff / 60);
            $minutes_diff = floor(($diff ) % 60);
            if ($data['diff'] >= 0){
                $data['diff']=$hours_diff .":". $minutes_diff;
            }else{
                $data['diff']='-'.$hours_diff .":". $minutes_diff;
            }

        }



        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof AttendanceTimes;
    }

    private function userHasPermissionsForBook($object): bool
    {
        // Get permissions from user in $this->tokenStorage
        // for the current $object (book) and
        // return true or false
    }
}
