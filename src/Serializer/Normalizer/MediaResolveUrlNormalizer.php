<?php

namespace App\Serializer\Normalizer;


use App\Entity\Media;
use App\Entity\PeriodStaffResult;
use App\Entity\Shift;
use App\Entity\ShiftHistory;
use App\Service\Timezone;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Vich\UploaderBundle\Storage\StorageInterface;

class MediaResolveUrlNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'MEDIA_RESOLVE_NORMALIZER_ALREADY_CALLED';
    /**
     * @var StorageInterface
     */
    private $storage;


    public function __construct(StorageInterface $storage)
    {

        $this->storage = $storage;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $data = $this->normalizer->normalize($object, $format, $context);
        $data["filePath"]= $this->storage->resolveUri($object, 'file');

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Media;
    }


}
