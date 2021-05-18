<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\AttendancePeriodEdit\AttendancePeriodEdit;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use App\Controller\AttendancePeriod\GetClosedByOrder;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"write_period","update_period"} },
 *     itemOperations={"get","put"={"controller"=AttendancePeriodEdit::class,"denormalization_context"={"groups"={"update_period"} } },"delete" },
 *     collectionOperations={"get",
 *      "getClosed"={
 *                  "method"="get",
 *                  "path"="/attendance_periods/getClosed",
 *                  "controller":GetClosedByOrder::class,
 *                  "swagger_context"={
 *                      "description"="use this api for dashboard get last closed and first unclosed",
 *                      "parameters"={{"name"="closed","in"="query","type"="boolean"}}
 *                   }
 *      }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\AttendancePeriodRepository")
 *
 */
class AttendancePeriod
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write_period","update_period"})
     */
    private $startTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write_period","update_period"})
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="attendancePeriods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $business;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"update_period"})
     */
    private $closed=false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PeriodStaffResult", mappedBy="attendancePeriod", orphanRemoval=true)
     */
    private $periodStaffResults;

    public function __construct()
    {
        $this->periodStaffResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

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

    /**
     * @return Collection|PeriodStaffResult[]
     */
    public function getPeriodStaffResults(): Collection
    {
        return $this->periodStaffResults;
    }

    public function addPeriodStaffResult(PeriodStaffResult $periodStaffResult): self
    {
        if (!$this->periodStaffResults->contains($periodStaffResult)) {
            $this->periodStaffResults[] = $periodStaffResult;
            $periodStaffResult->setAttendancePeriod($this);
        }

        return $this;
    }

    public function removePeriodStaffResult(PeriodStaffResult $periodStaffResult): self
    {
        if ($this->periodStaffResults->contains($periodStaffResult)) {
            $this->periodStaffResults->removeElement($periodStaffResult);
            // set the owning side to null (unless already changed)
            if ($periodStaffResult->getAttendancePeriod() === $this) {
                $periodStaffResult->setAttendancePeriod(null);
            }
        }

        return $this;
    }
}
