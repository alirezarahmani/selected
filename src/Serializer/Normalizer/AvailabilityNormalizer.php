<?php

namespace App\Serializer\Normalizer;


use App\Entity\AttendanceTimes;
use App\Entity\Availability;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;


class AvailabilityNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'AVAILABILITY_NORMALIZER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
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

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Availability;
    }

    private function userHasPermissionsForBook($object): bool
    {
        // Get permissions from user in $this->tokenStorage
        // for the current $object (book) and
        // return true or false
    }
}
