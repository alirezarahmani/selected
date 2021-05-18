<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Serializer\Annotation\Groups;
//no post method this just create by system when new user entity add to database
/**
 * @ApiResource(collectionOperations={"get"})
 * @ORM\Entity(repositoryClass="App\Repository\EmployeeAlertRepository")
 */
class EmployeeAlert
{
    const VALID_VALUES=['mobile'=>'mobile','email'=>'email'];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer"),
     * @Groups({"userread"})
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="employeeAlerts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"userread"})
     */
    private $timeOff=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"userread"})
     */
    private $swapDropShift=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"userread"})
     */
    private $scheduleUpdate=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"userread"})
     */
    private $newEmployee=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userread"})
     */
    private $availibilityChange=self::VALID_VALUES['email'];
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userread"})
     */
    private $clockReminder=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userread"})
     */
    private $overTimeAlert=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userread"})
     */
    private $payrollReminder=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userread"})
     */
    private $hireAlert=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"userread"})
     */
    private $shiftReminder=self::VALID_VALUES['email'];

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"userread"})
     */
    private $shiftRemiderClock=1;



    public function getId(): ?int
    {
        return $this->id;
    }


    public function getAvailibilityChange(): ?string
    {
        return $this->availibilityChange;
    }

    public function setAvailibilityChange(?string $availibilityChange): self
    {
        $this->availibilityChange = $availibilityChange;

        return $this;
    }

    public function getClockReminder(): ?string
    {
        return $this->clockReminder;
    }

    public function setClockReminder(?string $clockReminder): self
    {
        $this->clockReminder = $clockReminder;

        return $this;
    }

    public function getHireAlert(): ?string
    {
        return $this->hireAlert;
    }

    public function setHireAlert(?string $hireAlert): self
    {
        $this->hireAlert = $hireAlert;

        return $this;
    }

    public function getShiftRemiderClock(): ?int
    {
        return $this->shiftRemiderClock;
    }

    public function setShiftRemiderClock(?int $shiftRemiderClock): self
    {
        $this->shiftRemiderClock = $shiftRemiderClock;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getTimeOff()
    {
        return $this->timeOff;
    }

    /**
     * @param mixed $timeOff
     */
    public function setTimeOff($timeOff): void
    {
        $this->timeOff = $timeOff;
    }

    /**
     * @return mixed
     */
    public function getSwapDropShift()
    {
        return $this->swapDropShift;
    }

    /**
     * @param mixed $swapDropShift
     */
    public function setSwapDropShift($swapDropShift): void
    {
        $this->swapDropShift = $swapDropShift;
    }

    /**
     * @return mixed
     */
    public function getScheduleUpdate()
    {
        return $this->scheduleUpdate;
    }

    /**
     * @param mixed $scheduleUpdate
     */
    public function setScheduleUpdate($scheduleUpdate): void
    {
        $this->scheduleUpdate = $scheduleUpdate;
    }

    /**
     * @return mixed
     */
    public function getNewEmployee()
    {
        return $this->newEmployee;
    }

    /**
     * @param mixed $newEmployee
     */
    public function setNewEmployee($newEmployee): void
    {
        $this->newEmployee = $newEmployee;
    }

    /**
     * @return mixed
     */
    public function getOverTimeAlert()
    {
        return $this->overTimeAlert;
    }

    /**
     * @param mixed $overTimeAlert
     */
    public function setOverTimeAlert($overTimeAlert): void
    {
        $this->overTimeAlert = $overTimeAlert;
    }

    /**
     * @return mixed
     */
    public function getPayrollReminder()
    {
        return $this->payrollReminder;
    }

    /**
     * @param mixed $payrollReminder
     */
    public function setPayrollReminder($payrollReminder): void
    {
        $this->payrollReminder = $payrollReminder;
    }

    /**
     * @return mixed
     */
    public function getShiftReminder()
    {
        return $this->shiftReminder;
    }

    /**
     * @param mixed $shiftReminder
     */
    public function setShiftReminder($shiftReminder): void
    {
        $this->shiftReminder = $shiftReminder;
    }

}
