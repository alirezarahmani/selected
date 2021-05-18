<?php

namespace App\Serializer\Normalizer;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Business;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';

    private $tokenStorage;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(BusinessFinder $finder,IriConverterInterface $iriConverter)
    {

        $this->finder = $finder;
        $this->iriConverter = $iriConverter;
    }

    public function normalize($object, $format = null, array $context = [])
    {

         $context[self::ALREADY_CALLED] = true;
         $length=count($object->getAttendanceTimes()->getValues());
         $last=$length>0?$object->getAttendanceTimes()->getValues()[$length-1]:[];
         $object->setLastAttendanceTime($last);


         $user=$this->normalizer->normalize($object, $format, $context);
         if (array_key_exists('userBusinessRoles',$user)){
             /**
              * @var Business $business
              */
             $business=$this->finder->getCurrentUserBusiness();
             $user_business_roles=&$user['userBusinessRoles'];
             foreach ($user_business_roles as $key => $user_business_role){
                 if ($user_business_role['business']!== $this->iriConverter->getIriFromItem($business) ){
                     unset($user_business_roles[$key]);
                 }
             }
         }


         return $user;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }


}
