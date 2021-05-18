<?php

namespace App\Entity;
/*
 * author:javaheri.ghazaleh@gmail.com
 * logic on this models::
 *      on update shift's `start time` and `end time` all shift request attach this shift as requester or requested should be deleted
 *      on delete all shift history and shift request attached to this should be deleted
 *      on read this model all date in the model should be convert to login user timezone
 *      on read by user all shift that defines in the selected business should be retrieved
 *
 * */
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Filter\DateStringFilter;
use App\Controller\Shift\GetSelfShifts;
use App\Controller\Shift\TakeOpenShift;
use App\Controller\Shift\GetSwapShift;
use App\Controller\Shift\PublishAndNotify;
use App\Controller\Shift\ShiftsNotice;
use App\Controller\Shift\ScheduledComparison;
use App\Controller\Shift\UnPublishAndPublish;
use App\Controller\Shift\CountPublishAndUnPublish;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"shiftread"},"access_control"="is_granted('BUSINESS_EMPLOYEE')"},
 *     denormalizationContext={"groups"={"shiftwrite","eligible_item"}},
 *     itemOperations={"get","put",
 *          "delete"={
 *              "swagger_context"={
 *                  "parameters"={

 *                  {"name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={"chain"={"type"="boolean"} }
 *                  },{
 *                  "name"="id",
 *                  "type"="integer",
 *                  "in"="path"}
 *                  } } } },
 *     collectionOperations={
 *     "get"={"path"="/shifts"},"post",
 *     "selfShift"={
 *          "method"="get",
 *          "controller"=GetSelfShifts::class,
 *          "path"="/shifts/self",
 *          "swagger_context"={
 *              "summary":"return array of shift that are eligible or belongs to user"
 *          }

 *     },
 *
 *     "take_open_shift"={
 *          "method"="post",
 *          "controller"=TakeOpenShift::class,
 *          "path"="/shifts/take_open_shifts",
 *          "swagger_context"={"parameters"={{"name"="payload","in"="body","type"="object","properties"={"shift"={"type"="string"} } }} }
 *     },
 *
 *     "eligible_swap"={
 *          "method"="post",
 *          "path"="/shifts/eligible_swap",
 *          "controller"=GetSwapShift::class,
 *          "swagger_context"={
 *              "summary":"return array of shift that are eligible for swap with given shift",
 *              "parameters"={{"name"="payload","type"="object","in"="body","properties"={"shift"={"type"="string"} } }}
 *          }

 *     },
 *     "publish_notify"={
 *          "method"="post",
 *          "path"="/shifts/publish_and_notify",
 *          "controller"=PublishAndNotify::class,
 *          "swagger_context"={
 *              "summary":"notify user base on their employee alert and publish shifts in time ",
 *              "parameters"={{ "name"="payload","in"="body","properties"={"users":{"type":"array","items":"string"},"schedule":{"type":"string"},"start":{"type":"string"},"end":{"type":"string"},"notify_text":{"type":"string"},"changed_user":{"type":"boolean"} } }}
 *          }

 *     },
 *      "get_attendance_notice"={
 *          "method"="get",
 *          "path"="/shifts/notice",
 *          "controller"=ShiftsNotice::class

 *      },
 *     "get_scheduled_comparison_dashboard"={
 *          "method"="get",
 *          "path"="/shifts/scheduled_comparison_dashboard",
 *          "controller"=ScheduledComparison::class

 *      },
 *
 *     "publish_and_unpublish"={
 *          "method"="post",
 *          "path"="/shifts/publish_and_unpublish",
 *          "controller"=UnPublishAndPublish::class,
 *           "swagger_context"={
 *              "summary":"set password for first time",
 *              "parameters"={{ "name"="payload","in"="body","properties"={"users":{"type":"array","items":"string"},"shiftStartTime":{"type":"string"},"shiftEndTime":{"type":"string"},"publish":{"type":"boolean"} } }},
 *              "responses"={200={"description":"effected rows","schema"={"type":"integer"}},400={"description":"bad params"}}
 *          }
 *
 *      },
 *
 *     "get_count_publis_unpublish"={
 *          "method"="get",
 *          "controller"=CountPublishAndUnPublish::class,
 *          "path"="/shifts/count_publish_unpublish"

 *      }
 *
 *
 *     }
 *
 * )
 * @ApiFilter(SearchFilter::class,properties={"scheduleId":"exact","ownerId":"exact"}),
 * @ApiFilter(DateStringFilter::class,properties={"startTime":"after","endTime":"before"}),
 * @ORM\Entity(repositoryClass="App\Repository\ShiftRepository")
 */
class Shift
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"shiftread"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shift", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     */
    private $parentId=null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shift", mappedBy="parentId")
     */
    private $shifts;
    //nulllable true for open shifts
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"shiftread","shiftwrite","read_request"})
     */
    private $ownerId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"shiftread","shiftwrite","eligible_item","read_request"})
     */
    private $positionId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schedule", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"shiftread","shiftwrite","eligible_item","read_request"})
     */
    private $scheduleId;




    /**
     * @ORM\Column(type="boolean")
     * @Groups({"shiftread","shiftwrite"})
     */
    private $publish;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"shiftread","shiftwrite"})
     */
    private $confirm=false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"shiftread","shiftwrite"})
     */
    private $repeated=false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"shiftread","shiftwrite"})
     */
    private $repeatPeriod;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notification_message;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShiftHistory", mappedBy="shift_id", orphanRemoval=true)
     * @Groups({"shiftread"})
     */
    private $shiftHistories;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobSites", inversedBy="shifts")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     * @Groups({"shiftread","shiftwrite","eligible_item"})
     */
    private $jobSitesId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shiftwrite","shiftread","eligible_item","read_request","read_attendance_times"})
     */
    private $startTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shiftwrite","shiftread","eligible_item","read_request","read_attendance_times"})
     */
    private $endTime;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"shiftwrite","shiftread"})
     */
    private $endRepeatTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shiftwrite","shiftread","read_attendance_times"})
     */
    private $color;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"shiftwrite","shiftread"})
     */
    private $note;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"shiftwrite","shiftread","read_attendance_times"})
     */
    private $unpaidBreak=0;//this times is in minutes and deduct from total paid hours and deducts from worked if setting  Automatically Deduct Scheduled Breaks from Timesheets  is checked

    /**
     * @var boolean
     * @Groups({"shiftwrite"})
     */
    private $chain;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="eligibleForOpenShift"),
     * @ORM\JoinTable(name="eligibele_open_shift")
     * @Groups({"shiftwrite","shiftread"})
     */
    private $eligibleOpenShiftUser;
    //this field show  active shiftRequest when this is as requester shift
    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ShiftRequest", mappedBy="requesterShift", orphanRemoval=true)
     * @Groups({"shiftread"})
     */
    private $asRequesterShiftToRequest;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimes", mappedBy="shift")
     */
    private $attendanceTimes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $closed=false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("shiftread")
     */
    private $editable=true;//if user clockin a shift this shift is not editable after then



    /**
     * @ORM\Column(type="text")
     * @Groups("shiftread")
     */
    private $scheduled;//this shows how many minutes employee worked in this shifts

    /**
     * @ORM\Column(type="boolean")
     * @ApiProperty(
     *     attributes={
     *      "swagger_context"={
     *          "description"="this show owner of shift notified from shift or not?"}

     *     }
     *
     *)
     */
    private $informed=0;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Availability", inversedBy="conflictedShifts")
     * * @ApiProperty(
     *     attributes={
     *      "swagger_context"={
     *          "description"="array of avaol conflict by thi sshift"}

     *     })
     * @Groups({"shiftread"})
     */
    private $conflictAvailability;



    public function __construct()
    {
        $this->shifts = new ArrayCollection();
        $this->shiftHistories = new ArrayCollection();
        $this->eligibleOpenShiftUser = new ArrayCollection();
        $this->asRequesterShiftToRequest = new ArrayCollection();
        $this->attendanceTimes = new ArrayCollection();
        $this->conflictAvailability = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addShift(self $shift): self
    {
        if (!$this->shifts->contains($shift)) {
            $this->shifts[] = $shift;
            $shift->setParentId($this);
        }

        return $this;
    }

    public function removeShift(self $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getParentId() === $this) {
                $shift->setParentId(null);
            }
        }

        return $this;
    }

    public function getOwnerId(): ?User
    {
        return $this->ownerId;
    }

    public function setOwnerId(?User $ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    public function getPositionId(): ?Position
    {
        return $this->positionId;
    }

    public function setPositionId(?Position $positionId): self
    {
        $this->positionId = $positionId;

        return $this;
    }


    public function getScheduleId(): ?Schedule
    {
        return $this->scheduleId;
    }

    public function setScheduleId(?Schedule $scheduleId): self
    {
        $this->scheduleId = $scheduleId;

        return $this;
    }


    public function getPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        $this->publish = $publish;

        return $this;
    }

    public function getConfirm(): ?bool
    {
        return $this->confirm;
    }

    public function setConfirm(bool $confirm): self
    {
        $this->confirm = $confirm;

        return $this;
    }

    public function getRepeated(): ?bool
    {
        return $this->repeated;
    }

    public function setRepeated($repeated): self
    {
        $this->repeated = $repeated;

        return $this;
    }

    public function getRepeatPeriod(): ?int
    {
        return $this->repeatPeriod;
    }

    public function setRepeatPeriod(?int $repeatPeriod): self
    {
        $this->repeatPeriod = $repeatPeriod;

        return $this;
    }

    public function getNotificationMessage(): ?string
    {
        return $this->notification_message;
    }

    public function setNotificationMessage(?string $notification_message): self
    {
        $this->notification_message = $notification_message;

        return $this;
    }

    /**
     * @return Collection|ShiftHistory[]
     */
    public function getShiftHistories(): Collection
    {
        return $this->shiftHistories;
    }

    public function addShiftHistory(ShiftHistory $shiftHistory): self
    {
        if (!$this->shiftHistories->contains($shiftHistory)) {
            $this->shiftHistories[] = $shiftHistory;
            $shiftHistory->setShiftId($this);
        }

        return $this;
    }

    public function removeShiftHistory(ShiftHistory $shiftHistory): self
    {
        if ($this->shiftHistories->contains($shiftHistory)) {
            $this->shiftHistories->removeElement($shiftHistory);

        }

        return $this;
    }


    public function getJobSitesId(): ?JobSites
    {
        return $this->jobSitesId;
    }

    public function setJobSitesId(?JobSites $jobSitesId): self
    {
        $this->jobSitesId = $jobSitesId;

        return $this;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getEndRepeatTime(): ?string
    {
        return $this->endRepeatTime;
    }

    public function setEndRepeatTime(?string $endRepeatTime): self
    {
        $this->endRepeatTime = $endRepeatTime;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getUnpaidBreak(): ?int
    {
        return $this->unpaidBreak;
    }

    public function setUnpaidBreak(int $unpaidBreak): self
    {
        $this->unpaidBreak = $unpaidBreak;

        return $this;
    }

    /**
     * @return string
     */
    public function getChain(): ?string
    {
        return $this->chain;
    }

    /**
     * @param string $chain
     */
    public function setChain(?string $chain): void
    {
        $this->chain = $chain;
    }

    /**
     * @return Collection|User[]
     */
    public function getEligibleOpenShiftUser(): Collection
    {
        return $this->eligibleOpenShiftUser;
    }

    public function addEligibleOpenShiftUser(User $eligibleOpenShiftUser): self
    {
        if (!$this->eligibleOpenShiftUser->contains($eligibleOpenShiftUser)) {
            $this->eligibleOpenShiftUser[] = $eligibleOpenShiftUser;
        }

        return $this;
    }

    public function removeEligibleOpenShiftUser(User $eligibleOpenShiftUser): self
    {
        if ($this->eligibleOpenShiftUser->contains($eligibleOpenShiftUser)) {
            $this->eligibleOpenShiftUser->removeElement($eligibleOpenShiftUser);
        }

        return $this;
    }

    public function removeAllEligibilty()
    {
       foreach ($this->eligibleOpenShiftUser as $eligible){
           $this->eligibleOpenShiftUser->removeElement($eligible);
       }
        return $this;
    }

    /**
     * @return Collection|ShiftRequest[]
     */
    public function getAsRequesterShiftToRequest(): Collection
    {
        return $this->asRequesterShiftToRequest;
    }

    public function addAsRequesterShiftToRequest(ShiftRequest $asRequesterShiftToRequest): self
    {
        if (!$this->asRequesterShiftToRequest->contains($asRequesterShiftToRequest)) {
            $this->asRequesterShiftToRequest[] = $asRequesterShiftToRequest;
            $asRequesterShiftToRequest->setRequesterShift($this);
        }

        return $this;
    }

    public function removeAsRequesterShiftToRequest(ShiftRequest $asRequesterShiftToRequest): self
    {
        if ($this->asRequesterShiftToRequest->contains($asRequesterShiftToRequest)) {
            $this->asRequesterShiftToRequest->removeElement($asRequesterShiftToRequest);
            // set the owning side to null (unless already changed)
            if ($asRequesterShiftToRequest->getRequesterShift() === $this) {
                $asRequesterShiftToRequest->setRequesterShift(null);
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
            $attendanceTime->setShift($this);
        }

        return $this;
    }

    public function removeAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if ($this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes->removeElement($attendanceTime);
            // set the owning side to null (unless already changed)
            if ($attendanceTime->getShift() === $this) {
                $attendanceTime->setShift(null);
            }
        }

        return $this;
    }

    public function getClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getEditable(): ?bool
    {
        return $this->editable;
    }

    public function setEditable(bool $editable): self
    {
        $this->editable = $editable;

        return $this;
    }


    public function getScheduled(): ?string
    {
        return $this->scheduled;
    }

    public function setScheduled(string $scheduled): self
    {
        $this->scheduled = $scheduled;

        return $this;
    }

    public function getInformed(): ?bool
    {
        return $this->informed;
    }

    public function setInformed(bool $informed): self
    {
        $this->informed = $informed;

        return $this;
    }

    /**
     * @return Collection|Availability[]
     */
    public function getConflictAvailability(): Collection
    {
        return $this->conflictAvailability;
    }

    public function addConflictAvailability(Availability $conflictAvailability): self
    {
        if (!$this->conflictAvailability->contains($conflictAvailability)) {
            $this->conflictAvailability[] = $conflictAvailability;
        }

        return $this;
    }

    public function removeConflictAvailability(Availability $conflictAvailability): self
    {
        if ($this->conflictAvailability->contains($conflictAvailability)) {
            $this->conflictAvailability->removeElement($conflictAvailability);
        }

        return $this;
    }

    public function removeAllConflictedAvailability()
    {
     $this->conflictAvailability=new ArrayCollection();
     return $this;


    }

}
