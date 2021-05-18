<?php

namespace App\Entity;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Filter\DateStringFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Availability\GetWeekDays;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\Availability\EditAvailability;
use App\Controller\Availability\AddAvailability;


/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"writeavailability","editavailability"} },
 *     normalizationContext={"groups"={"readAvaialability"} },
 *     itemOperations={"get",
 *                     "put"={"controller"=EditAvailability::class ,"denormalization_context"={"groups"={"editavailability"} }, },
 *                     "delete"={
 *                           "swagger_context"={
 *                              "parameters"={

 *                                  {"name"="payload",
 *                                   "type"="object",
 *                                   "in"="body",
 *
 *                            "properties"={"chain"={"type"="boolean"} }
 *                               },{
 *                                  "name"="id",
 *                                  "type"="integer",
 *                                  "in"="path"}
 *                                  } } } },
 *     collectionOperations={
 *          "post"={"controller"=AddAvailability::class,"denormalization_context"={"groups"={"writeavailability"}} },
 *          "get",
 *          "days_of_week"={
 *              "method"="get",
 *              "path"="/availabilities/days",
 *              "controller"=GetWeekDays::class
 *          }

 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\AvailabilityRepository")
 * @ApiFilter(DateStringFilter::class,properties={"startTime":"after","endTime":"before"}),
 * @ApiFilter(SearchFilter::class,properties={"user":"exact"}),
 */
class Availability
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"readAvaialability"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Availability", inversedBy="availabilities",cascade={"persist"})
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     * @Groups({"readAvaialability"})
     */
    private $parentAvailable;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Availability", mappedBy="parentAvailable")
     * @Groups({"readAvaialability"})
     */
    private $availabilities;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="availabilities")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"writeavailability","readAvaialability"})
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     */
    private $note;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     */
    private $repeated;


    /**
     * @ORM\Column(type="boolean")
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     */
    private $available;

    /**
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     * @ORM\Column(type="text",nullable=true)
     *
     */
    private $days;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     */
    private $startTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="availabilities")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readAvaialability"})
     */
    private $businessId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"writeavailability","editavailability","readAvaialability"})
     */
    private $endReapetedTime;

    /**
     * @var boolean
     * @Groups({"editavailability"})
     */
    private $chain;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Shift", mappedBy="conflictAvailability")
     */
    private $conflictedShifts;



    public function __construct()
    {
        $this->availabilities = new ArrayCollection();
        $this->conflictedShifts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentAvailabilityId(): ?self
    {
        return $this->parentAvailable;
    }

    public function setParentAvailabilityId(?self $parentAvailable): self
    {
        $this->parentAvailable = $parentAvailable;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(self $availability): self
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities[] = $availability;
            $availability->setParentAvailabilityId($this);
        }

        return $this;
    }

    public function removeAvailability(self $availability): self
    {
        if ($this->availabilities->contains($availability)) {
            $this->availabilities->removeElement($availability);
            // set the owning side to null (unless already changed)
            if ($availability->getParentAvailabilityId() === $this) {
                $availability->setParentAvailabilityId(null);
            }
        }

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


    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getRepeated(): ?bool
    {
        return $this->repeated;
    }

    public function setRepeated(bool $repeated): self
    {
        $this->repeated = $repeated;

        return $this;
    }


    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getDays(): ?string
    {
        return $this->days;
    }

    public function setDays(?string $days): self
    {
        $this->days = $days;

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

    public function getBusinessId(): ?Business
    {
        return $this->businessId;
    }

    public function setBusinessId(?Business $businessId): self
    {
        $this->businessId = $businessId;

        return $this;
    }

    public function getEndReapetedTime(): ?string
    {
        return $this->endReapetedTime;
    }

    public function setEndReapetedTime(?string $endReapetedTime): self
    {
        $this->endReapetedTime = $endReapetedTime;

        return $this;
    }

    public function getChain(): ?bool
    {
        return $this->chain;
    }

    public function setChain(?bool $chain): self
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * @return Collection|Shift[]
     */
    public function getConflictedShifts(): Collection
    {
        return $this->conflictedShifts;
    }

    public function addConflictedShift(Shift $conflictedShift): self
    {
        if (!$this->conflictedShifts->contains($conflictedShift)) {
            $this->conflictedShifts[] = $conflictedShift;
            $conflictedShift->addConflictAvailability($this);
        }

        return $this;
    }

    public function removeConflictedShift(Shift $conflictedShift): self
    {
        if ($this->conflictedShifts->contains($conflictedShift)) {
            $this->conflictedShifts->removeElement($conflictedShift);
            $conflictedShift->removeConflictAvailability($this);
        }

        return $this;
    }

    public function removeAllConflictedShift()
    {
        $this->conflictedShifts=new ArrayCollection();
        return $this;
    }

    public function __toString()
    {
       return (string)$this->getId();
    }


}
