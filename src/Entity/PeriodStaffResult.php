<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Filter\DateStringFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read_results"} }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PeriodStaffResultRepository")
 * @ApiFilter(SearchFilter::class,properties={"user":"exact","attendancePeriod":"exact"})
 */
class PeriodStaffResult
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read_results"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AttendancePeriod", inversedBy="periodStaffResults")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_results"})
     */
    private $attendancePeriod;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="periodStaffResults")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_results"})
     */
    private $user;


    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_results"})
     */
    private $holiday;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_results"})
     */
    private $sick;


    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_results"})
     */
    private $total=0;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_results"})
     */
    private $labor;

    /**
     * @ORM\Column(type="text")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_results"})
     */
    private $ot=0;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_results"})
     */
    private $autoDeducted=0;

    /**
     * @ORM\Column(type="text")
     * @Groups({"read_results"})
     */
    private $totalScheduled=0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttendancePeriod(): ?AttendancePeriod
    {
        return $this->attendancePeriod;
    }

    public function setAttendancePeriod(?AttendancePeriod $attendancePeriod): self
    {
        $this->attendancePeriod = $attendancePeriod;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function getHoliday(): ?string
    {
        return $this->holiday;
    }

    public function setHoliday(?string $holiday): self
    {
        $this->holiday = $holiday;

        return $this;
    }

    public function getSick(): ?string
    {
        return $this->sick;
    }

    public function setSick(?string $sick): self
    {
        $this->sick = $sick;

        return $this;
    }


    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(?string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getLabor(): ?string
    {
        return $this->labor;
    }

    public function setLabor(?string $labor): self
    {
        $this->labor = $labor;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOt(): ?string
    {
        return $this->ot;
    }

    public function setOt(?string $ot): self
    {
        $this->ot = $ot;

        return $this;
    }

    public function getAutoDeducted(): ?string
    {
        return $this->autoDeducted;
    }

    public function setAutoDeducted(?string $autoDeducted): self
    {
        $this->autoDeducted = $autoDeducted;

        return $this;
    }

    public function getTotalScheduled(): ?string
    {
        return $this->totalScheduled;
    }

    public function setTotalScheduled(string $totalScheduled): self
    {
        $this->totalScheduled = $totalScheduled;

        return $this;
    }
}
