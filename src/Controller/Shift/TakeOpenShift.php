<?php


namespace App\Controller\Shift;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\EligiblerShift;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

class TakeOpenShift
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     * @var EligiblerShift
     */
    private $eligiblerShift;

    public function __construct(Security $security,EntityManagerInterface $manager,IriConverterInterface $iriConverter,EligiblerShift $eligiblerShift)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->iriConverter = $iriConverter;
        $this->eligiblerShift = $eligiblerShift;
    }

    /**
     * @param Request $request
     * @return Shift|object|null
     * @throws InvalidArgumentException
     * @throws ItemNotFoundException
     * @throws UnauthorizedHttpException
     */

    public function __invoke(Request $request)
    {
        /**
         * @var User $user
         */
        $user=$this->security->getUser();
        $request_content=json_decode($request->getContent(),true);
        if (!isset($request_content['shift']) ){
            throw new InvalidArgumentException('shift is required');
        }
        $id=$request_content['shift'];
        $shift=$this->iriConverter->getItemFromIri($id);

        $owner=$shift->getOwnerId();
        if (isset($owner)){
            throw new InvalidArgumentException('shift has user');
        }
        //only published shift can take
        $publish=$shift->getPublish();
        if (!$publish){
            throw new InvalidArgumentException('shift is not published');
        }

        //user can take a shift if be in both group 1-openShiftEligible 2-eligible_finder knows this user as eligible
        /**
         * @var Shift $shift
         * @var array $users
         */
        $users=$this->eligiblerShift->findOpenShiftEligible($shift->getStartTime(),$shift->getEndTime(),$shift->getScheduleId(),$shift->getPositionId());
        $in_array=false;
        foreach ($users as $item){
            if ($item->getId()=== $user->getId()){
                $in_array=true;
                break;
            }
        }
        if ($shift->getEligibleOpenShiftUser()->contains($user) && $in_array){
            $shift->setOwnerId($user);
            $shift->removeAllEligibilty();
            $this->manager->persist($shift);
            $this->manager->flush();
        }

        else
            throw new UnauthorizedHttpException('role','you are not eligible for this shift');

        //remove all other eligible because they should not be able too see this shift for their eligibility

        return $shift;


    }


}
