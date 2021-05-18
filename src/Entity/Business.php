<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Business\SelectBusiness;
use App\Controller\Business\FireEmployee;
use App\Controller\Business\TransferOwnership;
use App\Controller\Users\GetUserBusiness;
use App\Controller\Business\BusinessGetByDetail;
use App\Controller\Business\ActivateBusiness;
use App\Dto\BusinessOutput;


/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     normalizationContext={"groups"={"read","select","user_business_read"}},
 *     denormalizationContext={"groups"={"select","write","setting"}},
 *     itemOperations={
 *          "get",
 *          "put"={"denormalization_context"={"groups"={"setting"}} },
 *          "activate"={"method"="put","path"="/business/activate/{id}","controller":ActivateBusiness::class,"denormalization_context"={"groups"={"activate"} } },
 *          "delete"},
 *     collectionOperations={
 *     "post"={"denormalization_context"={"groups"={"write"} }},
 *     "get",
*      "get_usser_business"={
 *         "method"="GET",
 *         "path"="/businesses/self",
 *         "controller"=GetUserBusiness::class},
 *
 *
 *      "search_all_business"={
 *         "method"="GET",
 *         "path"="/businesses/search",
 *         "output": "App\Dto\BusinessOutput",
 *          "attributes"={"serialize"=false}
 *        },
 *
 *     "select"={
 *         "method"="POST",
 *         "path"="/business/select",
 *         "controller"=SelectBusiness::class,
 *         "swagger_context"={
 *              "summary"="user choose business to login",
 *              "parameters"={{
 *                  "in"="body",
 *                  "name"="payload",
 *                  "shema"="object",
 *                  "properties" = {
 *                      "id_business"={"type":"integer"} } }}  }
 *     },
 *     "get_by_owner_detail"={
 *         "method"="GET",
 *         "path"="/business/getAll",
 *         "controller"=BusinessGetByDetail::class,
 *         "swagger_context"={
 *              "summary"="this method only available for super-admin for getting business information"
 *          }
 *     },
 *     "fire_employee"={
 *          "method"="post",
 *          "controller"=FireEmployee::class,
 *          "path"="/business/fire_employee",
 *          "swagger_context"={
 *              "summary"="user choose business to login",
 *              "parameters"={{
 *                  "in"="body",
 *                  "name"="payload",
 *                  "shema"="object",
 *                  "properties" = {
 *                      "id_employee"={"type":"integer"} } }}  }

 *     },
 *     "transfer_ownership"={
 *          "method"="post",
 *          "defaults"={"_api_recieve":false},
 *          "controller"=TransferOwnership::class,
 *          "path"="/business/transfer_ownership",
 *          "swagger_context"={
 *              "summary"="user choose business to login",
 *              "parameters"={{
 *                  "in"="body",
 *                  "name"="payload",
 *                  "shema"="object",
 *                  "properties" = {
 *                      "user"={"type":"string"},
 *                      "refresh_bank"={"type"="boolean","description":"if true means last bank account be canceled"}
 *                  }
 *              }}
 *          }

 *     }
 * } )
 * @ORM\Entity(repositoryClass="App\Repository\BusinessRepository")
 * @ApiFilter(SearchFilter::class, properties={"name": "partial","address": "partial","country": "exact","billing": "exact"})
 */
//@todo add filter search for address and user(not in the business) and name
class Business
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"select","user_business_read"})
     */
    private $id;


    /**
     * @ORM\Column(type="text")
     * @Groups({"read","write","user_business_read","readbusinessreq","setting"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write","user_business_read","readbusinessreq","setting"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"setting","user_business_read"})
     *
     */
    private $timeFormat='D-M-Y HH:SS';

    /**
     * @ORM\Column(type="text")
     * @Groups({"setting","user_business_read"})
     */
    private $timeZone='Europe/London';



    /**
     * @ORM\Column(type="integer",nullable=true)
     * @Groups({"setting","user_business_read"})
     */
    private $maxDaysTimeOff;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"setting","user_business_read"})
     */
    private $setPreferred=true;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"setting","user_business_read"})
     */
    private $seePositionSchedule=true;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"setting","user_business_read"})
     */
    private $seeCoworkerSchedule=true;//

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"setting","user_business_read"})
     */
    private $shiftConfirmation=true;//require user confirmation it implement in ShiftWriteSubscriber

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"setting","user_business_read"})
     */
    private $approveTimeoffEmp=true;//in implement in time off request subscriber

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"setting","user_business_read"})
     */
    private $availability=true;//it implement in business setting implementation subscriber

    /**
     * @ORM\Column(type="string")
     * @Groups({"setting","user_business_read"})
     */
    private $expire_billing;



    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Position", mappedBy="business_id", orphanRemoval=true)
     */
    private $positions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Schedule", mappedBy="businessId", orphanRemoval=true)
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Industry", mappedBy="businessId", orphanRemoval=true)
     */
    private $industries;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Annotations", mappedBy="businessId", orphanRemoval=true)
     */
    private $annotations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobSites", mappedBy="businessId", orphanRemoval=true)
     */
    private $jobSites;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BusinessRequest", mappedBy="business", orphanRemoval=true)
     */
    private $businessRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserBusinessRole", mappedBy="business", orphanRemoval=true)
     */
    private $userBusinessRoles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BudgetTools", mappedBy="businessId", orphanRemoval=true)
     */
    private $budgetTools;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimeOffRequest", mappedBy="businessId", orphanRemoval=true)
     */
    private $timeOffRequests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Availability", mappedBy="businessId", orphanRemoval=true)
     */
    private $availabilities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimes", mappedBy="business", orphanRemoval=true)
     */
    private $attendanceTimes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendancePeriod", mappedBy="business", orphanRemoval=true)
     */
    private $attendancePeriods;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceSetting", mappedBy="business", orphanRemoval=true)
     */
    private $attendanceSettings;

    /**
     * @ORM\Column(type="text")
     * @Groups({"write","setting","user_business_read"})
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"write","setting","user_business_read"})
     */
    private $maxHourTimeoffPerDay=24;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimeOffTotal", mappedBy="businessId", orphanRemoval=true)
     * @ApiProperty(swaggerContext={"this field show how many hour a clerk is permit to take time off"})
     */
    private $timeOffTotals;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing", inversedBy="businesses")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","select"})
     */
    private $billing;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AllowedTerminalIp", mappedBy="business", orphanRemoval=true)
     */
    private $allowedTerminalIps;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BusinessBank", mappedBy="business")
     */
    private $businessBanks;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AvailableCountry", inversedBy="businesses")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"write","setting"})
     * @ApiProperty(attributes={"swagger_context"={"description":"this field should be checked on create business with location"}})
     */
    private $country;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="business", orphanRemoval=true)
     */
    private $notifications;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="businesses")
     * @Groups({"read","select"})
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BusinessCategory", inversedBy="businesses")
     * @Groups({"setting","read"})
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentHistory", mappedBy="business")
     */
    private $paymentHistories;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"activate","read"})
     */
    private $active=true;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $additionalUsersCount=0;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     * @Groups({"setting","write","read","select","user_business_read"})
     */
    private $image;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->annotations = new ArrayCollection();
        $this->jobSites = new ArrayCollection();
        $this->businessRequests = new ArrayCollection();
        $this->userBusinessRoles = new ArrayCollection();
        $this->budgetTools = new ArrayCollection();
        $this->timeOffRequests = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
        $this->attendanceTimes = new ArrayCollection();
        $this->attendancePeriods = new ArrayCollection();
        $this->attendanceSettings = new ArrayCollection();
        $this->timeOffTotals = new ArrayCollection();
        $this->allowedTerminalIps = new ArrayCollection();
        $this->businessBanks = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->paymentHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTimeFormat(): ?string
    {
        return $this->timeFormat;
    }

    public function setTimeFormat(string $timeFormat='D-M-Y HH:SS'): self
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone='en'): self
    {
        $this->timeZone = $timeZone;

        return $this;
    }


    public function getMaxDaysTimeOff(): ?int
    {
        return $this->maxDaysTimeOff;
    }

    public function setMaxDaysTimeOff(int $maxDaysTimeOff): self
    {
        $this->maxDaysTimeOff = $maxDaysTimeOff;

        return $this;
    }

    public function getSetPreferred(): ?bool
    {
        return $this->setPreferred;
    }

    public function setSetPreferred(bool $setPreferred): self
    {
        $this->setPreferred = $setPreferred;

        return $this;
    }

    public function getSeePositionSchedule(): ?bool
    {
        return $this->seePositionSchedule;
    }

    public function setSeePositionSchedule(bool $seePositionSchedule): self
    {
        $this->seePositionSchedule = $seePositionSchedule;

        return $this;
    }

    public function getSeeCoworkerSchedule(): ?bool
    {
        return $this->seeCoworkerSchedule;
    }

    public function setSeeCoworkerSchedule(bool $seeCoworkerSchedule): self
    {
        $this->seeCoworkerSchedule = $seeCoworkerSchedule;

        return $this;
    }

    public function getShiftConfirmation(): ?bool
    {
        return $this->shiftConfirmation;
    }

    public function setShiftConfirmation(bool $shiftConfirmation): self
    {
        $this->shiftConfirmation = $shiftConfirmation;

        return $this;
    }

    public function getApproveTimeoffEmp(): ?bool
    {
        return $this->approveTimeoffEmp;
    }

    public function setApproveTimeoffEmp(bool $approveTimeoffEmp): self
    {
        $this->approveTimeoffEmp = $approveTimeoffEmp;

        return $this;
    }

    public function getAvailability(): ?bool
    {
        return $this->availability;
    }

    public function setAvailability(bool $availability): self
    {
        $this->availability = $availability;

        return $this;
    }

    public function getExpireBilling(): ?string
    {
        return $this->expire_billing;
    }

    public function setExpireBilling(string $expire_billing): self
    {
        $this->expire_billing = $expire_billing;

        return $this;
    }



    /**
     * @return Collection|Position[]
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions[] = $position;
            $position->setBusinessId($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->contains($position)) {
            $this->positions->removeElement($position);
            // set the owning side to null (unless already changed)
            if ($position->getBusinessId() === $this) {
                $position->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Schedule[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setBusinessId($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): self
    {
        if ($this->schedules->contains($schedule)) {
            $this->schedules->removeElement($schedule);
            // set the owning side to null (unless already changed)
            if ($schedule->getBusinessId() === $this) {
                $schedule->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Industry[]
     */
    public function getIndustries(): Collection
    {
        return $this->industries;
    }

    public function addIndustry(Industry $industry): self
    {
        if (!$this->industries->contains($industry)) {
            $this->industries[] = $industry;
            $industry->setBusinessId($this);
        }

        return $this;
    }

    public function removeIndustry(Industry $industry): self
    {
        if ($this->industries->contains($industry)) {
            $this->industries->removeElement($industry);
            // set the owning side to null (unless already changed)
            if ($industry->getBusinessId() === $this) {
                $industry->setBusinessId(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Annotations[]
     */
    public function getAnnotations(): Collection
    {
        return $this->annotations;
    }

    public function addAnnotation(Annotations $annotation): self
    {
        if (!$this->annotations->contains($annotation)) {
            $this->annotations[] = $annotation;
            $annotation->setBusinessId($this);
        }

        return $this;
    }

    public function removeAnnotation(Annotations $annotation): self
    {
        if ($this->annotations->contains($annotation)) {
            $this->annotations->removeElement($annotation);
            // set the owning side to null (unless already changed)
            if ($annotation->getBusinessId() === $this) {
                $annotation->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|JobSites[]
     */
    public function getJobSites(): Collection
    {
        return $this->jobSites;
    }

    public function addJobSite(JobSites $jobSite): self
    {
        if (!$this->jobSites->contains($jobSite)) {
            $this->jobSites[] = $jobSite;
            $jobSite->setBusinessId($this);
        }

        return $this;
    }

    public function removeJobSite(JobSites $jobSite): self
    {
        if ($this->jobSites->contains($jobSite)) {
            $this->jobSites->removeElement($jobSite);
            // set the owning side to null (unless already changed)
            if ($jobSite->getBusinessId() === $this) {
                $jobSite->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BusinessRequest[]
     */
    public function getBusinessRequests(): Collection
    {
        return $this->businessRequests;
    }

    public function addBusinessRequest(BusinessRequest $businessRequest): self
    {
        if (!$this->businessRequests->contains($businessRequest)) {
            $this->businessRequests[] = $businessRequest;
            $businessRequest->setBusiness($this);
        }

        return $this;
    }

    public function removeBusinessRequest(BusinessRequest $businessRequest): self
    {
        if ($this->businessRequests->contains($businessRequest)) {
            $this->businessRequests->removeElement($businessRequest);
            // set the owning side to null (unless already changed)
            if ($businessRequest->getBusiness() === $this) {
                $businessRequest->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserBusinessRole[]
     */
    public function getUserBusinessRoles(): Collection
    {
        return $this->userBusinessRoles;
    }

    public function addUserBusinessRole(UserBusinessRole $userBusinessRole): self
    {
        if (!$this->userBusinessRoles->contains($userBusinessRole)) {
            $this->userBusinessRoles[] = $userBusinessRole;
            $userBusinessRole->setBusiness($this);
        }

        return $this;
    }

    public function removeUserBusinessRole(UserBusinessRole $userBusinessRole): self
    {
        if ($this->userBusinessRoles->contains($userBusinessRole)) {
            $this->userBusinessRoles->removeElement($userBusinessRole);
            // set the owning side to null (unless already changed)
            if ($userBusinessRole->getBusiness() === $this) {
                $userBusinessRole->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BudgetTools[]
     */
    public function getBudgetTools(): Collection
    {
        return $this->budgetTools;
    }

    public function addBudgetTool(BudgetTools $budgetTool): self
    {
        if (!$this->budgetTools->contains($budgetTool)) {
            $this->budgetTools[] = $budgetTool;
            $budgetTool->setBusinessId($this);
        }

        return $this;
    }

    public function removeBudgetTool(BudgetTools $budgetTool): self
    {
        if ($this->budgetTools->contains($budgetTool)) {
            $this->budgetTools->removeElement($budgetTool);
            // set the owning side to null (unless already changed)
            if ($budgetTool->getBusinessId() === $this) {
                $budgetTool->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TimeOffRequest[]
     */
    public function getTimeOffRequests(): Collection
    {
        return $this->timeOffRequests;
    }

    public function addTimeOffRequest(TimeOffRequest $timeOffRequest): self
    {
        if (!$this->timeOffRequests->contains($timeOffRequest)) {
            $this->timeOffRequests[] = $timeOffRequest;
            $timeOffRequest->setBusinessId($this);
        }

        return $this;
    }

    public function removeTimeOffRequest(TimeOffRequest $timeOffRequest): self
    {
        if ($this->timeOffRequests->contains($timeOffRequest)) {
            $this->timeOffRequests->removeElement($timeOffRequest);
            // set the owning side to null (unless already changed)
            if ($timeOffRequest->getBusinessId() === $this) {
                $timeOffRequest->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Availability[]
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): self
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities[] = $availability;
            $availability->setBusinessId($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): self
    {
        if ($this->availabilities->contains($availability)) {
            $this->availabilities->removeElement($availability);
            // set the owning side to null (unless already changed)
            if ($availability->getBusinessId() === $this) {
                $availability->setBusinessId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AttendanceTimes[]
     */
    public function getAttendanceTimes(): Collection
    {
        return $this->attendanceTimes;
    }

    public function addAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if (!$this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes[] = $attendanceTime;
            $attendanceTime->setBusiness($this);
        }

        return $this;
    }

    public function removeAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if ($this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes->removeElement($attendanceTime);
            // set the owning side to null (unless already changed)
            if ($attendanceTime->getBusiness() === $this) {
                $attendanceTime->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AttendancePeriod[]
     */
    public function getAttendancePeriods(): Collection
    {
        return $this->attendancePeriods;
    }

    public function addAttendancePeriod(AttendancePeriod $attendancePeriod): self
    {
        if (!$this->attendancePeriods->contains($attendancePeriod)) {
            $this->attendancePeriods[] = $attendancePeriod;
            $attendancePeriod->setBusiness($this);
        }

        return $this;
    }

    public function removeAttendancePeriod(AttendancePeriod $attendancePeriod): self
    {
        if ($this->attendancePeriods->contains($attendancePeriod)) {
            $this->attendancePeriods->removeElement($attendancePeriod);
            // set the owning side to null (unless already changed)
            if ($attendancePeriod->getBusiness() === $this) {
                $attendancePeriod->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AttendanceSetting[]
     */
    public function getAttendanceSettings(): Collection
    {
        return $this->attendanceSettings;
    }

    public function addAttendanceSetting(AttendanceSetting $attendanceSetting): self
    {
        if (!$this->attendanceSettings->contains($attendanceSetting)) {
            $this->attendanceSettings[] = $attendanceSetting;
            $attendanceSetting->setBusiness($this);
        }

        return $this;
    }

    public function removeAttendanceSetting(AttendanceSetting $attendanceSetting): self
    {
        if ($this->attendanceSettings->contains($attendanceSetting)) {
            $this->attendanceSettings->removeElement($attendanceSetting);
            // set the owning side to null (unless already changed)
            if ($attendanceSetting->getBusiness() === $this) {
                $attendanceSetting->setBusiness(null);
            }
        }

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getMaxHourTimeoffPerDay(): ?string
    {
        return $this->maxHourTimeoffPerDay;
    }

    public function setMaxHourTimeoffPerDay(?string $maxHourTimeoffPerDay): self
    {
        $this->maxHourTimeoffPerDay = $maxHourTimeoffPerDay;

        return $this;
    }

    /**
     * @return Collection|TimeOffTotal[]
     */
    public function getTimeOffTotals(): Collection
    {
        return $this->timeOffTotals;
    }

    public function addTimeOffTotal(TimeOffTotal $timeOffTotal): self
    {
        if (!$this->timeOffTotals->contains($timeOffTotal)) {
            $this->timeOffTotals[] = $timeOffTotal;
            $timeOffTotal->setBusinessId($this);
        }

        return $this;
    }

    public function removeTimeOffTotal(TimeOffTotal $timeOffTotal): self
    {
        if ($this->timeOffTotals->contains($timeOffTotal)) {
            $this->timeOffTotals->removeElement($timeOffTotal);
            // set the owning side to null (unless already changed)
            if ($timeOffTotal->getBusinessId() === $this) {
                $timeOffTotal->setBusinessId(null);
            }
        }

        return $this;
    }

    public function getBilling(): ?Billing
    {
        return $this->billing;
    }

    public function setBilling(?Billing $billing): self
    {
        $this->billing = $billing;

        return $this;
    }

    /**
     * @return Collection|AllowedTerminalIp[]
     */
    public function getAllowedTerminalIps(): Collection
    {
        return $this->allowedTerminalIps;
    }

    public function addAllowedTerminalIp(AllowedTerminalIp $allowedTerminalIp): self
    {
        if (!$this->allowedTerminalIps->contains($allowedTerminalIp)) {
            $this->allowedTerminalIps[] = $allowedTerminalIp;
            $allowedTerminalIp->setBusiness($this);
        }

        return $this;
    }

    public function removeAllowedTerminalIp(AllowedTerminalIp $allowedTerminalIp): self
    {
        if ($this->allowedTerminalIps->contains($allowedTerminalIp)) {
            $this->allowedTerminalIps->removeElement($allowedTerminalIp);
            // set the owning side to null (unless already changed)
            if ($allowedTerminalIp->getBusiness() === $this) {
                $allowedTerminalIp->setBusiness(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BusinessBank[]
     */
    public function getBusinessBanks(): Collection
    {
        return $this->businessBanks;
    }

    public function addBusinessBank(BusinessBank $businessBank): self
    {
        if (!$this->businessBanks->contains($businessBank)) {
            $this->businessBanks[] = $businessBank;
            $businessBank->setBusiness($this);
        }

        return $this;
    }

    public function removeBusinessBank(BusinessBank $businessBank): self
    {
        if ($this->businessBanks->contains($businessBank)) {
            $this->businessBanks->removeElement($businessBank);
            // set the owning side to null (unless already changed)
            if ($businessBank->getBusiness() === $this) {
                $businessBank->setBusiness(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?AvailableCountry
    {
        return $this->country;
    }

    public function setCountry(?AvailableCountry $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setBusiness($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getBusiness() === $this) {
                $notification->setBusiness(null);
            }
        }

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCategory(): ?BusinessCategory
    {
        return $this->category;
    }

    public function setCategory(?BusinessCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|PaymentHistory[]
     */
    public function getPaymentHistories(): Collection
    {
        return $this->paymentHistories;
    }

    public function addPaymentHistory(PaymentHistory $paymentHistory): self
    {
        if (!$this->paymentHistories->contains($paymentHistory)) {
            $this->paymentHistories[] = $paymentHistory;
            $paymentHistory->setBusiness($this);
        }

        return $this;
    }

    public function removePaymentHistory(PaymentHistory $paymentHistory): self
    {
        if ($this->paymentHistories->contains($paymentHistory)) {
            $this->paymentHistories->removeElement($paymentHistory);
            // set the owning side to null (unless already changed)
            if ($paymentHistory->getBusiness() === $this) {
                $paymentHistory->setBusiness(null);
            }
        }

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getAdditionalUsersCount(): ?int
    {
        return $this->additionalUsersCount;
    }

    public function setAdditionalUsersCount(int $additionalUsersCount): self
    {
        $this->additionalUsersCount = $additionalUsersCount;

        return $this;
    }

    public function getImage(): ?media
    {
        return $this->image;
    }

    public function setImage(?media $image): self
    {
        $this->image = $image;

        return $this;
    }
}

//@todo define timeFormats array

