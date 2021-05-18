<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
//this class save total time off for each user and show to admin and user when take time off
//deservedHoliday for all employee calculate by this formula:
/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\TimeOffTotalRepository")
 *
 *  @ApiFilter(SearchFilter::class,properties={"user":"exact"})
 */
class TimeOffTotal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="timeOffTotals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $totalHoliday=0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $totalSick=0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $deservedHoliday=0;//this is minutes deserved holiday for employee

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="timeOffTotals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTotalHoliday(): ?string
    {
        return $this->totalHoliday;
    }

    public function setTotalHoliday(?string $totalHoliday): self
    {
        $this->totalHoliday = $totalHoliday;

        return $this;
    }

    public function getTotalSick(): ?string
    {
        return $this->totalSick;
    }

    public function setTotalSick(?string $totalSick): self
    {
        $this->totalSick = $totalSick;

        return $this;
    }

    public function getDeservedHoliday(): ?string
    {
        return $this->deservedHoliday;
    }

    public function setDeservedHoliday(?string $deservdHoliday): self
    {
        $this->deservedHoliday = $deservdHoliday;

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
}
