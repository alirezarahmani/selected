<?php


namespace App\Controller\Shift;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Shift;
use App\Service\EligiblerShift;
use Symfony\Component\HttpFoundation\Request;

class GetSwapShift
{
    /**
     * @var EligiblerShift
     */
    private $eligiblerShift;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    public function __construct(EligiblerShift $eligiblerShift, IriConverterInterface $iriConverter)
    {
        $this->eligiblerShift = $eligiblerShift;
        $this->iriConverter = $iriConverter;
    }

    public function __invoke(Request $request)
    {
        $request_content=json_decode($request->getContent(),true);
        if (empty($request_content['shift']))
            throw new InvalidArgumentException('shift is required');
        $shift=$this->iriConverter->getItemFromIri($request_content['shift']);
        /**
         * @var Shift $shift
         */
        return $this->eligiblerShift->findSwapShiftEligible($shift);


    }

}
