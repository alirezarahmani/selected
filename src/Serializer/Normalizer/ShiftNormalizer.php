<?php

namespace App\Serializer\Normalizer;


use App\Entity\Shift;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ShiftNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'SHIFT_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
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


        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Shift;
    }


}
