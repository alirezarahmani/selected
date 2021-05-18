<?php


namespace App\Controller\Auth;


use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Bridge\Symfony\Routing\IriConverter;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Position;
use App\Entity\Schedule;
use App\Entity\TimeOffTotal;
use App\Entity\User;
use App\Entity\UserBusinessRole;
use App\Service\BusinessFinder;
use App\Service\TimeOffService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class EditUser
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var BusinessFinder
     */
    private $finder;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;
    /**
     * @var TimeOffService
     */
    private $timeOffService;

    public function __construct(EntityManagerInterface $manager,
                                BusinessFinder $finder,
                                Security $security,
                                ParameterBagInterface $bag,
                                IriConverterInterface $iriConverter,
                                TimeOffService $timeOffService,
                                SerializerInterface $serializer)
    {
        $this->manager = $manager;
        $this->finder = $finder;
        $this->security = $security;
        $this->serializer = $serializer;
        $this->bag = $bag;
        $this->iriConverter = $iriConverter;
        $this->timeOffService = $timeOffService;
    }

    public function __invoke($data,Request $request)
    {
      $user_id=$request->attributes->get('id');
        /**
         *@var User $user
         */
      $user=$data;
      $uow=$this->manager->getUnitOfWork();
        /**
         * @var User $old
         * @var User $data
         */

      $old=$uow->getOriginalEntityData($user);

      $req_content=json_decode($request->getContent(),true);
      $propertyAccessor = PropertyAccess::createPropertyAccessor();


      foreach ($req_content as $field=>$value){
          if ($field==='image'){
              $image=$this->iriConverter->getItemFromIri($value);
              $user->setImage($image);
              continue;
          }

          if ($field == 'userBusinessRoles'){
             if( $this->security->isGranted('USER_EDIT',$user)){
                 if (!array_key_exists($req_content[$field][0]['role'],$this->bag->get('roles'))){
                     throw new InvalidArgumentException('role not exists');
                 }
                 /**
                  * @var PersistentCollection $collections
                  */
                 $collections=$old['userBusinessRoles'];
                 $oldUserBusinessRoles=$collections->getSnapshot();
                 /**
                  * @var UserBusinessRole $usrBusinessRole
                  */
                 foreach ($oldUserBusinessRoles as $key=>$usrBusinessRole){
                     if ($usrBusinessRole->getBusiness() === $this->finder->getCurrentUserBusiness()){
                         //new $userBusinessRole generate and set props
                         $newUserBusinessRole=$req_content[$field][0];
                         $usrBusinessRole->setBusiness($this->finder->getCurrentUserBusiness());

                        //check user not decrease his access
                         $result=false;




                         foreach ($newUserBusinessRole as $item=>$val){
                             if ($item === 'baseHourlyRate' && !$this->security->isGranted('BUSINESS_MANAGER')){
                                 throw new UnauthorizedHttpException('role','you donot have permision to edit baseHourlyRate');
                             }
                             if ($item === 'role'){
                                 if ($this->security->getUser() === $user && $val!== $usrBusinessRole->getRole()){
                                     throw new UnauthorizedHttpException('role','you are not permitted to change your role ');
                                 }
                             }

                             if ($item === 'contract' && !array_key_exists('fixedDayesContract',$newUserBusinessRole)){
                                 throw  new InvalidArgumentException('you should fill fixed Dayes Contract for fixed contact');
                             }

                             if ($item === 'contract' && $val===UserBusinessRole::CONTRACTS[1]){
                                $totalTimeOffs= $this->manager->getRepository(TimeOffTotal::class)
                                     ->findBy(['user'=>$data,'businessId'=>$this->finder->getCurrentUserBusiness()]);

                                if (is_array($totalTimeOffs) && count($totalTimeOffs)>0 ){
                                    $totalTimeOff=$totalTimeOffs[0];
                                    $fixedDays=$newUserBusinessRole['fixedDayesContract'];
                                    $totalTimeOff->setDeservedHoliday($fixedDays*5.6*1440);//one day is 1440 minutes
                                    $this->manager->persist($totalTimeOff);
                                }

                             }
                             if ($item === 'contract' && $val===UserBusinessRole::CONTRACTS[0]){
                                $totalTimeOffs= $this->manager->getRepository(TimeOffTotal::class)
                                     ->findBy(['user'=>$data,'businessId'=>$this->finder->getCurrentUserBusiness()]);

                                if (is_array($totalTimeOffs) && count($totalTimeOffs)>0 ){
                                    $totalTimeOff=$totalTimeOffs[0];
                                     //calculate all clockIn till now
                                    $start= date('Y/01/01 00:00');
                                    $totalClockIn=$this->timeOffService->calculateHourlyEmpTimeOff($data,$start,$this->finder->getCurrentUserBusiness());
                                    $deserved_minutes= ((int)($totalClockIn)*12.07)/100;
                                    $totalTimeOff->setDeservedHoliday($deserved_minutes);//one day is 1440 minutes
                                    $this->manager->persist($totalTimeOff);
                                }

                             }

                             $propertyAccessor->setValue($usrBusinessRole,$item,$val);
                         }
                         $this->manager->persist($usrBusinessRole);
                         unset($req_content[$field]);
                     }
                 }
             }

              continue;

          }

          if ($field==='email' && (int)($this->security->getUser()->getId())!== (int)$user_id){
              throw new UnauthorizedHttpException('access','you not have permitted to change others email');
          }


          if ($field === 'positions' ){
            $user_other_business_position=array();
            $old_positions=$user->getPositions()->getSnapShot();
              /**
               * @var Position $position
               */
              //remove this business old position
            foreach ($old_positions as $position){
                if ($position->getBusinessId() === $this->finder->getCurrentUserBusiness()){
                    $user->removePosition($position);
                }
            }
              /**
               * @var Position $pos
               */
            foreach ($value as $p){
                $pos=$this->iriConverter->getItemFromIri($p);
                if ($pos->getBusinessId() === $this->finder->getCurrentUserBusiness())
                    $user->addPosition($pos);
                else{
                    throw new ItemNotFoundException(sprintf('%s not found in this business',$p));
                }
            }

           continue;


          }

          if ($field === 'userHasSchedule'){

              /**
               * @var PersistentCollection $collections
               */
              $collections=$old['userHasSchedule'];
              $oldSchedules=$collections->getSnapshot();

              $new_schedules=$value;
              $this_business_schedules=array();

              foreach ($new_schedules as $iri_schedule){
                  /**
                   * @var Schedule $schedule
                   */
                $schedule=$this->iriConverter->getItemFromIri($iri_schedule);
                if ($schedule->getBusinessId() !== $this->finder->getCurrentUserBusiness()){
                    throw new ItemNotFoundException(sprintf('%s not found'),$iri_schedule);
                }
                $this_business_schedules[]=$schedule;


              }//end foreach

              foreach ($oldSchedules as $key=>$schedule){
                  if ($schedule->getBusinessId() === $this->finder->getCurrentUserBusiness()){
                      unset($oldSchedules[$key]);
                      $user->removeUserHasSchedule($schedule);
                  }
              }//end foreach

              foreach ($this_business_schedules as $schedule){
                  $user->addUserHasSchedule($schedule);
              }//end foreach
              unset($req_content[$field]);
              continue;

          }

          if ($propertyAccessor->isWritable($user,$field)){
              $propertyAccessor->setValue($user,$field,$value);
          }

      }


      $this->manager->persist($user);
      $this->manager->flush();


      return $user;
    }


}
