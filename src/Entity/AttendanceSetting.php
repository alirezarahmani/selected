<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"write_attendance_setting","create_attendance_setting"} },
 *     collectionOperations={"get","post"={"denormalization_context"={"groups"={"create_attendance_setting"}} } },
 *     itemOperations={"get","put"={"denormalization_context"={"groups"={"write_attendance_setting"}} } }

 *      )
 * @ORM\Entity(repositoryClass="App\Repository\AttendanceSettingRepository")
 */
class AttendanceSetting
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"write_attendance_setting","create_attendance_setting"})
     */
    private $earlyLoginAllowed=15;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"write_attendance_setting","create_attendance_setting"})
     */
    private $nearByLocationDistance=250;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write_attendance_setting"})
     */
    private $clockInWithMobile=true;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write_attendance_setting"})
     */
    private $clockInByPc=false;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"write_attendance_setting","create_attendance_setting"})
     */
    private $payrollLengthDefault=14;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write_attendance_setting"})
     */
    private $alertClockIn=false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"write_attendance_setting"})
     */
    private $alertClockInMinutes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="attendanceSettings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $business;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write_attendance_setting"})
     */
    private $registerBreak=true;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write_attendance_setting"})
     */
    private $automateCalculateBreak=false;

    /**
     * @ORM\Column(type="boolean")
     *  @Groups({"write_attendance_setting"})
     */
    private $alertClockInManager=false;

    /**
     * @ORM\Column(type="boolean")
     * @ApiProperty(attributes={"swagger_context"={"summery"="if true only allowed ip can clock in","example"="true"} })
     */
    private $restrictIpforPersonalAttendance=true;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEarlyLoginAllowed(): ?int
    {
        return $this->earlyLoginAllowed;
    }

    public function setEarlyLoginAllowed(int $earlyLoginAllowed): self
    {
        $this->earlyLoginAllowed = $earlyLoginAllowed;

        return $this;
    }

    public function getNearByLocationDistance(): ?int
    {
        return $this->nearByLocationDistance;
    }

    public function setNearByLocationDistance(int $nearByLocationDistance): self
    {
        $this->nearByLocationDistance = $nearByLocationDistance;

        return $this;
    }

    public function getClockInWithMobile(): ?bool
    {
        return $this->clockInWithMobile;
    }

    public function setClockInWithMobile(bool $clockInWithMobile): self
    {
        $this->clockInWithMobile = $clockInWithMobile;

        return $this;
    }

    public function getClockInByPc(): ?bool
    {
        return $this->clockInByPc;
    }

    public function setClockInByPc(bool $clockInByPc): self
    {
        $this->clockInByPc = $clockInByPc;

        return $this;
    }

    public function getPayrollLengthDefault(): ?int
    {
        return $this->payrollLengthDefault;
    }

    public function setPayrollLengthDefault(int $payrollLengthDefault): self
    {
        $this->payrollLengthDefault = $payrollLengthDefault;

        return $this;
    }

    public function getAlertClockIn(): ?bool
    {
        return $this->alertClockIn;
    }

    public function setAlertClockIn(bool $alertClockIn): self
    {
        $this->alertClockIn = $alertClockIn;

        return $this;
    }

    public function getAlertClockInMinutes(): ?int
    {
        return $this->alertClockInMinutes;
    }

    public function setAlertClockInMinutes(?int $alertClockInMinutes): self
    {
        $this->alertClockInMinutes = $alertClockInMinutes;

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

    public function getRegisterBreak(): ?bool
    {
        return $this->registerBreak;
    }

    public function setRegisterBreak(bool $registerBreak): self
    {
        $this->registerBreak = $registerBreak;

        return $this;
    }

    public function getAutomateCalculateBreak(): ?bool
    {
        return $this->automateCalculateBreak;
    }

    public function setAutomateCalculateBreak(bool $automateCalculateBreak): self
    {
        $this->automateCalculateBreak = $automateCalculateBreak;

        return $this;
    }

    public function getAlertClockInManager(): ?bool
    {
        return $this->alertClockInManager;
    }

    public function setAlertClockInManager(bool $alertClockInManager): self
    {
        $this->alertClockInManager = $alertClockInManager;

        return $this;
    }

    public function getRestrictIpforPersonalAttendance(): ?bool
    {
        return $this->restrictIpforPersonalAttendance;
    }

    public function setRestrictIpforPersonalAttendance(bool $restrictIpforPersonalAttendance): self
    {
        $this->restrictIpforPersonalAttendance = $restrictIpforPersonalAttendance;

        return $this;
    }

}
