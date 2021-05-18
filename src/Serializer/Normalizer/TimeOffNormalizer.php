<?php

namespace App\Serializer\Normalizer;


use App\Entity\TimeOffRequest;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;


class TimeOffNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TIME_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';

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

        if (array_key_exists('startTime',$data)){
            $data['startTime']=$this->timezone->transformSystemDateToUser($data['startTime']);
        }
        if (array_key_exists('endTime',$data)){
            $data['endTime']=$this->timezone->transformSystemDateToUser($data['endTime']);

        }
        if (array_key_exists('createdAt',$data)){
            $data['createdAt']=$this->timezone->transformSystemDateToUser($data['createdAt']);
        }



        return $data;

    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof TimeOffRequest;
    }

    private function userHasPermissionsForBook($object): bool
    {
        // Get permissions from user in $this->tokenStorage
        // for the current $object (book) and
        // return true or false
    }
}
