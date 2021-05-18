<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Positions\UnscheduledPositions;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('BUSINESS_EMPLOYEE')"},
 *     denormalizationContext={"groups"={"writeposition"}},
 *     normalizationContext={"groups"={"readposition","userread","shifttemplateread"}},
 *     collectionOperations={
 *      "get"={"path"="/positions"},
 *      "post"={
 *          "denormalization_context"={"groups"={"writeposition"}},
 *          "access_control"="is_granted('BUSINESS_SUPERVISOR')"
*       },
 *     "get_unscheduled_positions"={
 *          "method"="Post",
 *          "path"="/positions/unscheduled",
 *          "controller"=UnscheduledPositions::class,
 *          "swagger_context"={
 *              "summary"="find unscheduled positions in a date range",
 *              "parameters"={
 *                  {
 *                      "name"="payload",
 *                      "in"="body",
 *                      "type"="object",
 *                      "properties"={"start_date":{"type"="string"},"end_date"={"type":"string"},"schedule"={"type":"string"}}

 *                  }} }  }
 *      })
 *
 *
 * @ORM\Entity(repositoryClass="App\Repository\PositionRepository")
 */
class Position
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"readposition","userread","read_attendance_times"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writeposition","readposition","userread","shifttemplateread","shiftread","read_request","read_attendance_times"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writeposition","readposition","shiftread"})
     */
    private $color;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"writeposition","readposition","shiftread"})
     */
    private $favorite=false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $business_id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="positions", cascade="persist")
     */
    private $employee_has_position;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shift", mappedBy="positionId")
     */
    private $shifts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShiftTemplate", mappedBy="positionId", orphanRemoval=true)
     */
    private $shiftTemplates;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimes", mappedBy="position")
     */
    private $attendanceTimes;

    public function __construct()
    {
        $this->employee_has_position = new ArrayCollection();
        $this->shifts = new ArrayCollection();
        $this->shiftTemplates = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): self
    {
        $this->favorite = $favorite;

        return $this;
    }

    public function getBusinessId(): ?Business
    {
        return $this->business_id;
    }

    public function setBusinessId(?Business $business_id): self
    {
        $this->business_id = $business_id;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getEmployeeHasPosition(): Collection
    {
        return $this->employee_has_position;
    }

    public function addEmployeeHasPosition(User $employeeHasPosition): self
    {
        if (!$this->employee_has_position->contains($employeeHasPosition)) {
            $this->employee_has_position[] = $employeeHasPosition;
        }

        return $this;
    }

    public function removeEmployeeHasPosition(User $employeeHasPosition): self
    {
        if ($this->employee_has_position->contains($employeeHasPosition)) {
            $this->employee_has_position->removeElement($employeeHasPosition);
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
            $shift->setPositionId($this);
        }

        return $this;
    }

    public function removeShift(Shift $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getPositionId() === $this) {
                $shift->setPositionId(null);
            }
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
            $shiftTemplate->setPositionId($this);
        }

        return $this;
    }

    public function removeShiftTemplate(ShiftTemplate $shiftTemplate): self
    {
        if ($this->shiftTemplates->contains($shiftTemplate)) {
            $this->shiftTemplates->removeElement($shiftTemplate);
            // set the owning side to null (unless already changed)
            if ($shiftTemplate->getPositionId() === $this) {
                $shiftTemplate->setPositionId(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
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
            $attendanceTime->setPosition($this);
        }

        return $this;
    }

    public function removeAttendanceTime(AttendanceTimes $attendanceTime): self
    {
        if ($this->attendanceTimes->contains($attendanceTime)) {
            $this->attendanceTimes->removeElement($attendanceTime);
            // set the owning side to null (unless already changed)
            if ($attendanceTime->getPosition() === $this) {
                $attendanceTime->setPosition(null);
            }
        }

        return $this;
    }


}
