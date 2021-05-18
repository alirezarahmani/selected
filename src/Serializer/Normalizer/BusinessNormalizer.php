<?php

namespace App\Serializer\Normalizer;


use App\Entity\AttendanceTimes;
use App\Entity\Business;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;


class BusinessNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'BUSINESS_NORMALIZER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
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
        /**
         * @var Business $object
         */
        $data["numberOfEmployee"]=count($object->getUserBusinessRoles()->getValues());



        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Business;
    }

}
