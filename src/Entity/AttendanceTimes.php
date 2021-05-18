<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Filter\DateStringFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Controller\AttendanceTimes\SetAdminTokenTerminal;
use App\Controller\AttendanceTimes\LoginTerminal;
use App\Controller\AttendanceTimes\LoginTerminalAuth;
use App\Controller\AttendanceTimes\OnTimeEstimate;
//how breaks work:
//user on each break start and break end add times to break
// if user forgot register each step log register to notify admin
//admin edit break out break end by updating break field
/**
 * @ApiResource(
 *
 *     attributes={"validation_groups"={"updatetimes"}},
 *     denormalizationContext={"groups"={"write_times","updatetimes"} },
 *     normalizationContext={"groups"={"read_attendance_times"}},
 *     collectionOperations={"POST"={ "swagger_context"={"summary"="use for personal computer if attendnace setting ,restrictIpforPersonalAttendance, be true only restricted ip can clock in" } },
 *                          "GET",
 *                          "onTimeAlertForEmployee"={
 *                               "method"="get",
 *                               "defaults"={"_api_receive"=false},
 *                               "path"="/attendance_times/onTimeSelf",
 *                               "controller"=OnTimeEstimate::class,
 *                               "swagger_context"={
 *                                  "summary":"this api indicates percentage you attend tour workplace ontime"
 *                                       }
 *                          },
 *                          "login_terminal_auth"={
 *                               "method"="POST",
 *                               "defaults"={"_api_receive"=false},
 *                               "path"="/login_terminal_auth",
 *                               "controller"=LoginTerminalAuth::class,
 *                               "swagger_context"={
 *                               "summary":"login in terminal first step ",
 *                                        "parameters"={{ "name"="payload",
 *                                                         "in"="body",
 *                                                       "properties"={
 *                                                           "header":{"type":"string"},
 *                                                           "email":{"type":"string","description": "user email because this field is unique for user"},
 *                                                       }
 *                                           }},
 *                                        "responses"={201={"description":"clock in successfullu","schema"={"type":"boolean"}},400={"description":"bad params"}}
 *                                    }
 *                          },
 *                         "login_with_admin_token"={
 *                               "method"="POST",
 *                               "defaults"={"_api_receive"=false},
 *                               "path"="/login_terminal",
 *                               "controller"=LoginTerminal::class,
 *                               "swagger_context"={
 *                               "summary":"login in terminal",
 *                                        "parameters"={{ "name"="payload",
 *                                                         "in"="body",
 *                                                       "properties"={
 *                                                           "media":{"type":"string","example":"/api/media/1"},
 *                                                           "startTime":{"type":"string"},
 *                                                           "header":{"type":"string"},
 *                                                           "breakoutStart":{"type":"string","example":"2020-04-04 16:20"},
 *                                                           "breakOutEnd":{"type":"string","example":"2020-04-04 16:40"},
 *                                                           "position":{"type":"string","example":"/api/positions/1"},
 *                                                           "mail":{"type":"string","description": "user email because this field is unique for user"},
 *                                                       }
 *                                           }},
 *                                        "responses"={201={"description":"clock in successfullu","schema"={"type":"boolean"}},400={"description":"bad params"}}
 *                                    }
 *                          },
 *                          "get_admin_terminal_token"={
 *                               "method"="POST",
 *                               "defaults"={"_api_receive"=false},
 *                               "path"="/getTerminalToken",
 *                               "controller"=SetAdminTokenTerminal::class,
 *                               "swagger_context"={
 *                               "summary":"get terminal token",
 *                                        "parameters"={{ "name"="payload",
*                                                         "in"="body",
 *                                                        "properties"={
 *                                                           "schedule":{"type":"string"},
 *                                                           "jobsite":{"type":"string","description": "iri of from one of the business jobsites that clock in and clock out location set from its lat &lng"},
 *                                                       }
 *                                           }},
 *                                        "responses"={200={"description":"token generate successfully","schema"={"type":"string"}},400={"description":"bad params"}}
 *                                    }

 *                          }
 *     },
 *     itemOperations={"GET","PUT"={"denormalization_context"={"groups"={"updatetimes"}} ,"attributes"={"validation_groups"={"updatetimes"}} } ,"DELETE" }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\AttendanceTimesRepository")
 * @ApiFilter(DateStringFilter::class,properties={"startTime":"after","endTime":"before"}),
 * @ApiFilter(SearchFilter::class,properties={"user":"exact"}),
 * @ApiFilter(OrderFilter::class, properties={"id"}, arguments={"orderParameterName"="order"})
 */
class AttendanceTimes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read_attendance_times","userread"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="attendanceTimes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_attendance_times"})
     */
    private $business;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="attendanceTimes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"write_times","read_attendance_times"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schedule", inversedBy="attendanceTimes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"write_times","read_attendance_times","updatetimes"})
     */
    private $schedule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="attendanceTimes", cascade="persist")
     * @Groups({"write_times","read_attendance_times","updatetimes"})
     */
    private $position;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_attendance_times","write_times"})
     */
    private $clockInLocation;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_attendance_times","write_times","updatetimes"})
     */
    private $clockOutLocation;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"write_times","updatetimes","read_attendance_times","userread"})
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private $startTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"updatetimes","read_attendance_times","userread"})
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shift", inversedBy="attendanceTimes")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"read_attendance_times","write_times"})
     */
    private $shift;


    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"write_times","read_attendance_times","updatetimes","userread"})
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private $breakoutStart;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"write_times","read_attendance_times","updatetimes","userread"})
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private $breakOutEnd;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_attendance_times"})
     */
    private $worked;//minutes worked

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read_attendance_times"})
     */
    private $info;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"updatetimes","read_attendance_times","userread"})
     */
    private $break=0;//seconds break

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendanceTimesLog", mappedBy="attendanceTime", orphanRemoval=true)
     * @Groups({"read_attendance_times"})
     */
    private $attendanceTimesLogs;

    /**
     * @var string
     * @Groups({"write_times","updatetimes"})
     * @ApiProperty(deprecationReason="this field sent in request when clock in terminal to determine")
     */
    private $header;//this field sent in request when clock in terminal to determine

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media")
     * @Groups({"write_times","updatetimes"})
     * @ApiProperty(attributes={"swagger_context"={"example"="/api/media/1"} })
     */
    private $media;


    public function __construct()
    {
        $this->attendanceTimesLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getClockInLocation(): ?string
    {
        return $this->clockInLocation;
    }

    public function setClockInLocation(?string $clockInLocation): self
    {
        $this->clockInLocation = $clockInLocation;

        return $this;
    }

    public function getClockOutLocation(): ?string
    {
        return $this->clockOutLocation;
    }

    public function setClockOutLocation(?string $clockOutLocation): self
    {
        $this->clockOutLocation = $clockOutLocation;

        return $this;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(?string $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function setShift(?Shift $shift): self
    {
        $this->shift = $shift;

        return $this;
    }





    public function getBreakoutStart(): ?string
    {
        return $this->breakoutStart;
    }

    public function setBreakoutStart(?string $breakoutStart): self
    {
        $this->breakoutStart = $breakoutStart;

        return $this;
    }

    public function getBreakOutEnd(): ?string
    {
        return $this->breakOutEnd;
    }

    public function setBreakOutEnd(?string $breakOutEnd): self
    {
        $this->breakOutEnd = $breakOutEnd;

        return $this;
    }

    public function getWorked(): ?string
    {
        return $this->worked;
    }

    public function setWorked(?string $worked): self
    {
        $this->worked = $worked;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getBreak(): ?string
    {
        return $this->break;
    }

    public function setBreak(?string $break): self
    {
        $this->break = $break;

        return $this;
    }

    /**
     * @return Collection|AttendanceTimesLog[]
     */
    public function getAttendanceTimesLogs(): Collection
    {
        return $this->attendanceTimesLogs;
    }

    public function addAttendanceTimesLog(AttendanceTimesLog $attendanceTimesLog): self
    {
        if (!$this->attendanceTimesLogs->contains($attendanceTimesLog)) {
            $this->attendanceTimesLogs[] = $attendanceTimesLog;
            $attendanceTimesLog->setAttendanceTime($this);
        }

        return $this;
    }

    public function removeAttendanceTimesLog(AttendanceTimesLog $attendanceTimesLog): self
    {
        if ($this->attendanceTimesLogs->contains($attendanceTimesLog)) {
            $this->attendanceTimesLogs->removeElement($attendanceTimesLog);
            // set the owning side to null (unless already changed)
            if ($attendanceTimesLog->getAttendanceTime() === $this) {
                $attendanceTimesLog->setAttendanceTime(null);
            }
        }

        return $this;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(?string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }



}
