<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"userwrite"}},
 *     normalizationContext={"groups"={"userread","user_business_read","read"}}
 *     ),
 * @ORM\Entity(repositoryClass="App\Repository\UserBusinessRoleRepository")
 * @ORM\Table(
 *   name="user_business_role",
 *   uniqueConstraints={@UniqueConstraint(name="unique_in_business",columns={"user_id","business_id"})},
 * )
 */
class UserBusinessRole
{
    const CONTRACTS=['zero','fixed'];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userBusinessRoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="userBusinessRoles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user_business_read","userread","shiftread"})
     */
    private $business;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"userwrite","userread","user_business_read"})
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userwrite","userread","shiftread"})
     */
    private $baseHourlyRate=0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userwrite","userread"})
     */
    private $maxHoursWeek;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"userwrite","userread"})
     */
    private $calculateOT=false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userwrite","userread"})
     */
    private $payrollOT=0;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"userwrite","userread"})
     */
    private $editTimeSheet=false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"userwrite","userread"})
     */
    private $hideInScheduler=false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userwrite","userread"})
     */
    private $terminalId;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"userread"})
     */
    private $active=false;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"userwrite","userread"})
     * @Assert\Choice(choices=UserBusinessRole::CONTRACTS, message="Choose a valid contract.")
     */
    private $contract=self::CONTRACTS[0];


    /**
     * @ORM\Column(type="integer")
     * @Groups({"userwrite","userread"})
     */
    private $fixedDayesContract=0;

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

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getBaseHourlyRate()
    {
        return $this->baseHourlyRate;
    }

    public function setBaseHourlyRate($baseHourlyRate): self
    {
        $this->baseHourlyRate = $baseHourlyRate;

        return $this;
    }

    public function getMaxHoursWeek(): ?string
    {
        return $this->maxHoursWeek;
    }

    public function setMaxHoursWeek(?string $maxHoursWeek): self
    {
        $this->maxHoursWeek = $maxHoursWeek;

        return $this;
    }

    public function getCalculateOT(): ?bool
    {
        return $this->calculateOT;
    }

    public function setCalculateOT(bool $calculateOT): self
    {
        $this->calculateOT = $calculateOT;

        return $this;
    }

    public function getPayrollOT(): ?string
    {
        return $this->payrollOT;
    }

    public function setPayrollOT(?string $payrollOT): self
    {
        $this->payrollOT = $payrollOT;

        return $this;
    }

    public function getEditTimeSheet(): ?bool
    {
        return $this->editTimeSheet;
    }

    public function setEditTimeSheet(bool $editTimeSheet): self
    {
        $this->editTimeSheet = $editTimeSheet;

        return $this;
    }

    public function getHideInScheduler(): ?bool
    {
        return $this->hideInScheduler;
    }

    public function setHideInScheduler(?bool $hideInScheduler): self
    {
        $this->hideInScheduler = $hideInScheduler;

        return $this;
    }

    public function getTerminalId(): ?string
    {
        return $this->terminalId;
    }

    public function setTerminalId(?string $terminalId): self
    {
        $this->terminalId = $terminalId;

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

    public function getContract(): ?string
    {
        return $this->contract;
    }

    public function setContract(string $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getFixedDayesContract(): ?int
    {
        return $this->fixedDayesContract;
    }

    public function setFixedDayesContract(int $fixedDayesContract): self
    {
        $this->fixedDayesContract = $fixedDayesContract;

        return $this;
    }
}
