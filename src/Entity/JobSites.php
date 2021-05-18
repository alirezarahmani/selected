<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource(
 *     attributes={"filters"={"jobsites.search_filter"}},
 *     denormalizationContext={"groups"={"writejobsites"}},
 *     normalizationContext={"groups"={"readjobsites"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\JobSitesRepository")
 * @ApiFilter(SearchFilter::class,properties={"schedules":"exact"})
 */
class JobSites
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"readjobsites","shiftread"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Schedule", inversedBy="jobSites")
     * @Groups({"writejobsites","readjobsites"})
     */
    private $schedules;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="jobSites")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $businessId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writejobsites","readjobsites","shiftread"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"writejobsites","readjobsites"})
     */
    private $address;

    /**
     * @ORM\Column(type="text")
     * @Groups({"writejobsites","readjobsites"})
     */
    private $lat;

    /**
     * @ORM\Column(type="text")
     * @Groups({"writejobsites","readjobsites"})
     */
    private $lang;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writejobsites","readjobsites"})

     */
    private $color;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"writejobsites","readjobsites"})
     */
    private $note;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shift", mappedBy="jobSitesId")
     */
    private $shifts;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
        $this->shifts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): self
    {
        if ($this->schedules->contains($schedule)) {
            $this->schedules->removeElement($schedule);
        }

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

    public function getLat()
    {
        return $this->lat;
    }

    public function setLat($lat): self
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
            $shift->setJobSitesId($this);
        }

        return $this;
    }

    public function removeShift(Shift $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getJobSitesId() === $this) {
                $shift->setJobSitesId(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
