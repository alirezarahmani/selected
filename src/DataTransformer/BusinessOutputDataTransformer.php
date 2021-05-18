<?php


namespace App\DataTransformer;



use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\BusinessOutput;
use App\Entity\Business;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BusinessOutputDataTransformer implements  DataTransformerInterface
{
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    public function transform($object, string $to, array $context = [])
    {
        $user_business_roles=$object->getUserBusinessRoles()->getValues();
        $output = new BusinessOutput();
       foreach($user_business_roles as $ro){
           if ($ro->getRole()===$this->bag->get('roles')['account'])
               $output->owner=$ro->getUser()->getFirstName().' '.$ro->getUser()->getLastName();
       }

        $output->name = $object->getName();
        $output->address = $object->getAddress();
        $output->id=$object->getId();
        $output->image=$object->getImage();

        return $output;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return BusinessOutput::class === $to && $data instanceof Business;
    }
}
