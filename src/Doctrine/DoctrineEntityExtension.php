<?php


namespace App\Doctrine;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Controller\Auth\ConfirmMobile;
use App\Controller\Auth\ResendVerification;
use App\Entity\AttendancePeriod;
use App\Entity\AttendanceSetting;
use App\Entity\AttendanceTimes;
use App\Entity\Availability;
use App\Entity\BudgetTools;
use App\Entity\Business;
use App\Entity\BusinessRequest;
use App\Entity\JobSites;
use App\Entity\PaymentHistory;
use App\Entity\PeriodStaffResult;
use App\Entity\Position;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\ShiftRequest;
use App\Entity\ShiftTemplate;
use App\Entity\TimeOffRequest;
use App\Entity\User;
use App\Service\BusinessFinder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class DoctrineEntityExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    /**
     * @var BusinessFinder
     */
    private $businessFinder;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(BusinessFinder $businessFinder,Security $security,RequestStack $requestStack)
    {
        $this->businessFinder = $businessFinder;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {

        if ($this->security->isGranted('ROLE_SUPER_ADMIN'))
            return;
        $controller=$this->requestStack->getCurrentRequest()->attributes->get('_controller');
        if($controller!==ConfirmMobile::class && $controller!== ResendVerification::class)
            $this->addWhereUsers($queryBuilder,$resourceClass);
        $this->addWhereJobSites($queryBuilder,$resourceClass);
        $this->addWhereSchedule($queryBuilder,$resourceClass);
        $this->addWherePositions($queryBuilder,$resourceClass);
        $this->addWhereShiftTemplate($queryBuilder,$resourceClass);
        $this->addWhereTimeOff($queryBuilder,$resourceClass);
        $this->addWhereBudgetTools($queryBuilder,$resourceClass);
        $this->andWhereBusinessRequest($queryBuilder,$resourceClass,$operationName);
        $this->addWhereShifts($queryBuilder,$resourceClass);
        $this->andWhereShiftRequests($queryBuilder,$resourceClass);
        $this->addWhereAttendanceSetting($queryBuilder,$resourceClass);
        $this->andWhereAvailability($queryBuilder,$resourceClass);
        $this->addWhereAttendanceTimes($queryBuilder,$resourceClass);
        $this->addWhereStaffPeriodResult($queryBuilder,$resourceClass);
        $this->andWherePaymentHistory($queryBuilder,$resourceClass);
        $this->andWhereAttendancePeriod($queryBuilder,$resourceClass);


    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN'))
            return;

        $this->addWhereUsers($queryBuilder,$resourceClass);
        $this->addWhereJobSites($queryBuilder,$resourceClass);
        $this->addWhereSchedule($queryBuilder,$resourceClass);
        $this->addWherePositions($queryBuilder,$resourceClass);
        $this->addWhereShiftTemplate($queryBuilder,$resourceClass);
        $this->addWhereTimeOff($queryBuilder,$resourceClass);
        $this->addWhereBudgetTools($queryBuilder,$resourceClass);
        $this->andWhereBusinessRequest($queryBuilder,$resourceClass,$operationName);
        $this->addWhereShifts($queryBuilder,$resourceClass);
        $this->andWhereShiftRequests($queryBuilder,$resourceClass);
        $this->addWhereAttendanceSetting($queryBuilder,$resourceClass);
        $this->andWhereAvailability($queryBuilder,$resourceClass);
        $this->addWhereAttendanceTimes($queryBuilder,$resourceClass);
        $this->addWhereStaffPeriodResult($queryBuilder,$resourceClass);
        $this->andWherePaymentHistory($queryBuilder,$resourceClass);
        $this->andWhereAttendancePeriod($queryBuilder,$resourceClass);

    }

    private function andWhereAvailability(QueryBuilder $queryBuilder,string $resourceClass)
    {
        if ($resourceClass === Availability::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $job_root=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId',$job_root),$business_id));
        }
    }

    private function addWhereJobSites(QueryBuilder $queryBuilder,string $resourceClass)
    {
        if ($resourceClass === JobSites::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $job_root=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId',$job_root),$business_id));
        }

    }

    private function addWhereSchedule(QueryBuilder $queryBuilder,string $resourceClass){

        if ($resourceClass=== Schedule::class){
            /**
             * @var Business $business
             */


            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $she=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId',$she),$business_id));

        }


    }

    private function addWhereTimeOff(QueryBuilder $queryBuilder,string $resourceClass){

        if ($resourceClass=== TimeOffRequest::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $she=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId',$she),$business_id));
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
                $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.userID',$she),$this->security->getUser()->getId()));
            }
        }


    }

    private function addWherePositions(QueryBuilder $queryBuilder,string $resourceClass){

        if ($resourceClass === Position::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $pos_root=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.business_id',$pos_root),$business_id));
        }

    }

    private function addWhereShiftTemplate(QueryBuilder $queryBuilder,string $resourceClass)
    {
        if ($resourceClass === ShiftTemplate::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $shift_root=$rootAliases[0];
            $queryBuilder->leftJoin(sprintf('%s.scheduleId',$shift_root),'sch')->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId','sch'),$business_id));
            $business=$this->businessFinder->getCurrentUserBusiness();
            if (!$business->getBilling()->getUseScheduler()){
                throw new \ApiPlatform\Core\Exception\InvalidArgumentException("billing");
            }
        }


    }

    private function addWhereUsers(QueryBuilder $queryBuilder,string $resourceClass)
    {
        if($resourceClass === User::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $user_root=$rootAliases[0];
            $queryBuilder->leftJoin(sprintf('%s.userBusinessRoles',$user_root),'ubr')
                         ->andwhere($queryBuilder->expr()->eq('ubr.business',$business_id));
        }

    }

    private function addWhereBudgetTools(QueryBuilder $queryBuilder,string $resourceClass)
    {

        if ($resourceClass === BudgetTools::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $budget_root=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId',$budget_root),$business_id));
        }

    }

    public function andWhereBusinessRequest(QueryBuilder $queryBuilder,string $resourceClass,string $operationName)
    {
        if ($resourceClass === BusinessRequest::class && $operationName ==="get"){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $business_req_tool=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.business',$business_req_tool),$business_id));
            $queryBuilder->andWhere($queryBuilder->expr()->like(sprintf('%s.status',$business_req_tool),"'".BusinessRequest::BUSINESS_REQUEST_SUSPEND."'"));
        }
    }

    public function addWhereShifts(QueryBuilder $queryBuilder,string $resourceClass)
    {

        if ($resourceClass === Shift::class){

            /**
             * @var User $user
             */
            $user=$this->security->getUser();
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases()[0];
            $queryBuilder->leftJoin(sprintf('%s.scheduleId',$rootAliases),'sche');
            $queryBuilder->leftJoin(sprintf('%s.eligibleOpenShiftUser',$rootAliases),'eligible');
            $queryBuilder->andWhere($queryBuilder->expr()->eq('sche.businessId',$business_id));
            $business=$this->businessFinder->getCurrentUserBusiness();
            if (!$business->getBilling()->getUseScheduler()){
                throw new \ApiPlatform\Core\Exception\InvalidArgumentException("billing");
            }

            //if followng activate swap and drop not work in employee
//            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
//                $eligibility=array();
//                $userShiftEligible=($user->getEligibleForOpenShift()->getValues());
//                foreach ($userShiftEligible as $shift){
//                    $eligibility[]=$shift->getId();
//                }
//                if (count($eligibility)>0){
//                    $queryBuilder->andWhere(
//                        $queryBuilder->expr()->orX(
//                        $queryBuilder->expr()->eq(sprintf('%s.ownerId',$rootAliases),$user->getId()),
//                        $queryBuilder->expr()->in('eligible.id',$eligibility)
//                            ));
//                }else{
//                   $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.ownerId',$rootAliases),$user->getId()));
//                }
//
//
//            }



        }

    }

    public function andWhereShiftRequests(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass === ShiftRequest::class) {
            /**
             * @var User $user
             */
            $user = $this->security->getUser();
            $business_id = $this->businessFinder->getUserBusiness();
            $rootAliases = $queryBuilder->getRootAliases()[0];
            $queryBuilder->leftJoin(sprintf('%s.swaps',$rootAliases),'swap');
            $queryBuilder->leftJoin(sprintf('%s.requesterShift',$rootAliases),'shift');
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.businessId',$rootAliases),$business_id));
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX($queryBuilder->expr()->eq('swap.user',$user->getId()),
                    $queryBuilder->expr()->eq('shift.ownerId',$user->getId()))
                    );
            }

        }

    }

    public function addWhereAttendanceSetting(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass === AttendanceSetting::class){

            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $budget_root=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.business',$budget_root),$business_id));

        }

    }

    public function addWhereAttendanceTimes(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass === AttendanceTimes::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $attendance=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.business',$attendance),$business_id));
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
                $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.user',$attendance),$this->security->getUser()->getId()));

            }
            /**
             * @var Business $business
             */
            $business=$this->businessFinder->getCurrentUserBusiness();
            if (!$business->getBilling()->getUseAttendance()){
                throw new \ApiPlatform\Core\Exception\InvalidArgumentException("billing");
            }


        }

    }

    public function addWhereStaffPeriodResult(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass === PeriodStaffResult::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $staffPeriodResult=$rootAliases[0];
            if (!$this->security->isGranted('BUSINESS_SUPERVISOR')){
                $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.user',$staffPeriodResult),$this->security->getUser()->getId()));

            }


        }

    }


    public function andWhereAttendancePeriod(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass === AttendancePeriod::class){
            $business_id=$this->businessFinder->getUserBusiness();

            $rootAliases=$queryBuilder->getRootAliases();
            $staffPeriodResult=$rootAliases[0];

            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.business',$staffPeriodResult),$business_id));
            $request=($this->requestStack->getCurrentRequest());


        }

    }


    private function andWherePaymentHistory(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass=== PaymentHistory::class){
            $business_id=$this->businessFinder->getUserBusiness();
            $rootAliases=$queryBuilder->getRootAliases();
            $she=$rootAliases[0];
            $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.business',$she),$business_id));
        }
    }



}
