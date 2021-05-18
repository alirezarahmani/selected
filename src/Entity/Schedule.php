<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Schedule\ScheduleShiftCount;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('BUSINESS_EMPLOYEE')"},
 *     denormalizationContext={"groups"={"schwrite"}},
 *     normalizationContext={"groups"={"userread","schread","readjobsites","shifttemplateread"}},
 *     collectionOperations={"get"={"path"="/schedules"},
 *     "post",
 *     "shift_count"={
 *          "method"="post",
 *          "controller"=ScheduleShiftCount::class,
 *          "path"="/schedules/shift_count",
 *          "swagger_context"={
 *              "parameters"={{"in"="body","name"="payload","type"="object","properties"={"schedule"={"type"="string"} } }}
 *          }

 *     }}
 *     )
 *
 * @ORM\Entity(repositoryClass="App\Repository\ScheduleRepository")
 */
class Schedule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"schwrite","schread","userread","readjobsites","read_attendance_times","annotation_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"schwrite","userread","schread","readjobsites","shifttemplateread","annotation_read","shiftread","read_request","read_attendance_times"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"schwrite","schread","read_attendance_times"})
     */
    private $address;

    /**
     * @ORM\Column(type="text")
     * @Groups({"schwrite","schread","read_attendance_times"})
     */
    private $lat;

    /**
     * @ORM\Column(type="text")
     * @Groups({"schwrite","schread","read_attendance_times"})
     */
    private $lang;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"schwrite","schread"})
     */
    private $maxHourWeek=0;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"schwrite"})
     */
    private $ipAddress;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="schedules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Annotations", mappedBy="scheduleId", orphanRemoval=true)
     */
    private $annotations;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="userHasSchedule")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shift", mappedBy="scheduleId", orphanRemoval=true)
     */
    private $shifts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\JobSites", mappedBy="schedules")
     */
    private $jobSites;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShiftTemplate", mappedBy="scheduleId", orphanRemoval=true)
     */
    private $shiftTemplates;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BudgetTools", mappedBy="scheduleId", orphanRemoval=true)
     */
    private $budgetTools;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimes", mappedBy="schedule")
     */
    private $attendanceTimes;

    public function __construct()
    {
        $this->annotations = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->shifts = new ArrayCollection();
        $this->jobSites = new ArrayCollection();
        $this->shiftTemplates = new ArrayCollection();
        $this->budgetTools = new ArrayCollection();
        $this->attendanceTimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getMaxHourWeek(): ?string
    {
        return $this->maxHourWeek;
    }

    public function setMaxHourWeek(string $maxHourWeek): self
    {
        $this->maxHourWeek = $maxHourWeek;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getBusinessId(): ?Business
    {
        return $this->businessId;
    }

    public function setBusinessId(?Business $businessId): self
    {
        $this->businessId = $businessId;

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
            $annotation->setScheduleId($this);
        }

        return $this;
    }

    public function removeAnnotation(Annotations $annotation): self
    {
        if ($this->annotations->contains($annotation)) {
            $this->annotations->removeElement($annotation);
            // set the owning side to null (unless already changed)
            if ($annotation->getScheduleId() === $this) {
                $annotation->setScheduleId(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addUserHasSchedule($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeUserHasSchedule($this);
        }

        return $this;
    }

    /**
     * @return Collection|Shift[]
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addShift(Shift $shift): self
    {
        if (!$this->shifts->contains($shift)) {
            $this->shifts[] = $shift;
            $shift->setScheduleId($this);
        }

        return $this;
    }

    public function removeShift(Shift $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getScheduleId() === $this) {
                $shift->setScheduleId(null);
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
            $jobSite->addSchedule($this);
        }

        return $this;
    }

    public function removeJobSite(JobSites $jobSite): self
    {
        if ($this->jobSites->contains($jobSite)) {
            $this->jobSites->removeElement($jobSite);
            $jobSite->removeSchedule($this);
        }

        return $this;
    }

    /**
     * @return Collection|ShiftTemplate[]
     */
    public function getShiftTemplates(): Collection
    {
        return $this->shiftTemplates;
    }

    public function addShiftTemplate(ShiftTemplate $shiftTemplate): self
    {
        if (!$this->shiftTemplates->contains($shiftTemplate)) {
            $this->shiftTemplates[] = $shiftTemplate;
            $shiftTemplate->setScheduleId($this);
        }

        return $this;
    }

    public function removeShiftTemplate(ShiftTemplate $shiftTemplate): self
    {
        if ($this->shiftTemplates->contains($shiftTemplate)) {
            $this->shiftTemplates->removeElement($shiftTemplate);
            // set the owning side to null (unless already changed)
            if ($shiftTemplate->getScheduleId() === $this) {
                $shiftTemplate->setScheduleId(null);
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
            $budgetTool->setScheduleId($this);
        }

        return $this;
    }

    public function removeBudgetTool(BudgetTools $budgetTool): self
    {
        if ($this->budgetTools->contains($budgetTool)) {
            $this->budgetTools->removeElement($budgetTool);
            // set the owning side to null (unless already changed)
            if ($budgetTool->getScheduleId() === $this) {
                $budgetTool->setScheduleId(null);
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
            $attendanceTime->setSchedule($this);
        }

        return $this;
    }

    public function removeAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if ($this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes->removeElement($attendanceTime);
            // set the owning side to null (unless already changed)
            if ($attendanceTime->getSchedule() === $this) {
                $attendanceTime->setSchedule(null);
            }
        }

        return $this;
    }
}
