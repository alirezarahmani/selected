<?php

namespace App\Serializer\Normalizer;


use App\Entity\AttendanceTimes;
use App\Entity\AttendanceTimesLog;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;


class AttendanceTimesLogsNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ATTENDANCE_LOG_NORMALIZER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
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

        if(array_key_exists('time',$data) && !is_null($data['time'])){
            $time=$data['time'];
            $time_transformed=$this->timezone->transformSystemDateToUser($time);
            $data['time']=$time_transformed;

        }
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof AttendanceTimesLog;
    }


}
