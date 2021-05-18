<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Filter\DateStringFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AttendanceTimesLogRepository")
 * @ApiFilter(DateStringFilter::class,properties={"time":"exact"}),

 */
class AttendanceTimesLog
{
    const TYPES = ['break', 'warn','absent'];//log are currently  category
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AttendanceTimes", inversedBy="attendanceTimesLogs")
     * @ORM\JoinColumn(nullable=true)
     */
    private $attendanceTime;

    /**
     * @ORM\Column(type="text",nullable=true)
     * @Groups("read_attendance_times")
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Choice(choices=AttendanceTimesLog::TYPES, message="Choose a valid type.")
     * @Groups("read_attendance_times")
     */
    private $type;//it should be in enum[break,warn]

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups("read_attendance_times")
     */
    private $time;//time should be save in seprate filed to be convertable in diff timezone it exactly should be attendance time and day of week it saved


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="attendanceTimesLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;//THIS FIELD MAKE IT Possible to load log for specific day for specific user
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttendanceTime(): ?AttendanceTimes
    {
        return $this->attendanceTime;
    }

    public function setAttendanceTime(?AttendanceTimes $attendanceTime): self
    {
        $this->attendanceTime = $attendanceTime;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(?string $time): self
    {
        $this->time = $time;

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
}
