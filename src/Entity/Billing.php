<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}},
 *
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BillingRepository")
 */
class Billing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"write","read"})

     */
    private $period;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write","read"})
     */
    private $numberOfEmployee;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write","read"})
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write","read","user_business_read"})
     */
    private $useAttendance;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write","read"})
     */
    private $useHiring;



    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write","read"})
     */
    private $isDefault;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write","read"})
     */
    private $active=true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Business", mappedBy="billing")
     */
    private $businesses;


    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write","read","user_business_read"})
     */
    private $useScheduler=true;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write","read","user_business_read"})
     */
    private $useAvailability=true;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write","read"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="billings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"write","read"})
     */
    private $currency;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentHistory", mappedBy="billing")
     */
    private $paymentHistories;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     * @Groups({"read","write"})
     */
    private $image;

    public function __construct()
    {
        $this->businesses = new ArrayCollection();
        $this->paymentHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getNumberOfEmployee(): ?string
    {
        return $this->numberOfEmployee;
    }

    public function setNumberOfEmployee(string $numberOfEmployee): self
    {
        $this->numberOfEmployee = $numberOfEmployee;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getUseAttendance(): ?bool
    {
        return $this->useAttendance;
    }

    public function setUseAttendance(bool $useAttendance): self
    {
        $this->useAttendance = $useAttendance;

        return $this;
    }

    public function getUseHiring(): ?bool
    {
        return $this->useHiring;
    }

    public function setUseHiring(bool $use_hiring): self
    {
        $this->useHiring = $use_hiring;

        return $this;
    }


    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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

    /**
     * @return Collection|Business[]
     */
    public function getBusinesses(): Collection
    {
        return $this->businesses;
    }

    public function addBusiness(Business $business): self
    {
        if (!$this->businesses->contains($business)) {
            $this->businesses[] = $business;
            $business->setBilling($this);
        }

        return $this;
    }

    public function removeBusiness(Business $business): self
    {
        if ($this->businesses->contains($business)) {
            $this->businesses->removeElement($business);
            // set the owning side to null (unless already changed)
            if ($business->getBilling() === $this) {
                $business->setBilling(null);
            }
        }

        return $this;
    }

    public function getUseScheduler(): ?bool
    {
        return $this->useScheduler;
    }

    public function setUseScheduler(bool $useScheduler): self
    {
        $this->useScheduler = $useScheduler;

        return $this;
    }

    public function getUseAvailability(): ?bool
    {
        return $this->useAvailability;
    }

    public function setUseAvailability(bool $useAvailability): self
    {
        $this->useAvailability = $useAvailability;

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

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

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
            $paymentHistory->setBilling($this);
        }

        return $this;
    }

    public function removePaymentHistory(PaymentHistory $paymentHistory): self
    {
        if ($this->paymentHistories->contains($paymentHistory)) {
            $this->paymentHistories->removeElement($paymentHistory);
            // set the owning side to null (unless already changed)
            if ($paymentHistory->getBilling() === $this) {
                $paymentHistory->setBilling(null);
            }
        }

        return $this;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(?Media $image): self
    {
        $this->image = $image;

        return $this;
    }
}
//@todo define currency array
//@todo define number_of_employee
