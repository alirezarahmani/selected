<?php

namespace App\Serializer\Normalizer;


use App\Entity\PeriodStaffResult;
use App\Entity\Shift;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class PeriodStaffResultNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PERIODSTAFFRESULTNORMALIZER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';
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
//        if (array_key_exists('regular',$data)){
//            $hour_reg=floor($data['regular']/60);
//            $min_reg=floor(($data['regular'])%60);
//            $data['regular']=$hour_reg.' : '.$min_reg;
//        }
//        if (array_key_exists('pto',$data)){
//            $hour_pto=floor($data['pto']/60);
//            $min_pto=floor(($data['pto'])%60);
//            $data['pto']=$hour_pto.' : '.$min_pto;
//        }
//        if (array_key_exists('holiday',$data)){
//            $hour_holiday=floor($data['holiday']/60);
//            $min_holiday=floor(($data['holiday'])%60);
//            $data['holiday']=$hour_holiday.' : '.$min_holiday;
//        }
//        if (array_key_exists('sick',$data)){
//            $hour_sick=floor($data['sick']/60);
//            $min_sick=floor(($data['sick'])%60);
//            $data['sick']=$hour_sick.' : '.$min_sick;
//        }
//        if (array_key_exists('diff',$data)){
//            $hour_diff=floor($data['diff']/60);
//            $min_diff=floor(($data['diff'])%60);
//            $data['diff']=$hour_diff.' : '.$min_diff;
//        }
//        if (array_key_exists('total',$data)){
//            $hour_total=floor($data['total']/60);
//            $min_total=floor(($data['total'])%60);
//            $data['total']=$hour_total.' : '.$min_total;
//        }
//        if (array_key_exists('ot',$data)){
//            $ot=$data['ot'];
//            $hours_ot = floor($ot / 60);
//            $minutes_ot = floor(($ot ) % 60);
//            $data['ot']=$hours_ot .":". $minutes_ot;
//        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof PeriodStaffResult;
    }


}
