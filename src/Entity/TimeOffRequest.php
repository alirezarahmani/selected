<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\TimeOffRequest\TimeOffRequestTypeController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\TimeOffRequest\GetValidStatus;
use App\Filter\DateStringFilter;


/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"writetimeoffreq","updateTimeOffRequest"} },
 *     normalizationContext={"groups"={"readtimeoffreq"} },
 *     itemOperations={"get","put"={"denormalization_context"={"groups"={"updateTimeOffRequest"} } }},
 *     collectionOperations={
 *          "get"={"path"="/time_off_requests"},
 *          "post"={"denormalization_context"={"groups"={"writetimeoffreq"} } },
 *          "getTimeOffStatus"={
 *              "summery"="get time off status",
 *              "method"="get",
 *              "controller"=GetValidStatus::class,
 *              "path"="/time_off_requests/status"
 *          },
 *          "getTimeOffType"={
 *              "summary"="get time off type",
 *              "method"="get",
 *              "controller"=TimeOffRequestTypeController::class,
 *              "path"="/time_off_requests/types",
 *              "swagger_context"={
 *                  "responses"={
 *                      "200"={
 *                          "description":"ok",
 *                          "schema"={"type"="object"} },
 *                      "400"={
 *                          "description":"bad request"}

 *                  }

 *              }
 *          }
 *     }

 *     )
 * @ORM\Entity(repositoryClass="App\Repository\TimeOffRequestRepository")
 * @ApiFilter(DateStringFilter::class,properties={"startTime":"after","endTime":"before"}),

 */
class TimeOffRequest
{
     const TIME_OFF_ACCEPT='accepted';
     const TIME_OFF_DENIED='denied';
     const TIME_OFF_CREATED='created';
     const TIME_OFF_CANCELED='canceled';


    //timeOffTypes
    const TIME_OFF_TYPE=['paid','sick','holiday'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"readtimeoffreq"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="timeOffRequests")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"writetimeoffreq","readtimeoffreq"})
     */
    private $userID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="requested_timeoff")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"writetimeoffreq","readtimeoffreq"})
     */
    private $userCreatorId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writetimeoffreq","readtimeoffreq"})
     * @Assert\Choice(choices=TimeOffRequest::TIME_OFF_TYPE, message="Choose a valid genre.")
     */
    private $type;



    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Groups({"writetimeoffreq","readtimeoffreq"})
     */
    private $paidHour=0;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"writetimeoffreq","readtimeoffreq"})
     */
    private $message;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimeoffLog", mappedBy="timeOffRequstId", orphanRemoval=true)
     * @Groups({"readtimeoffreq"})
     */
    private $timeoffLogs;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writetimeoffreq","readtimeoffreq"})

     */
    private $startTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"writetimeoffreq","readtimeoffreq"})

     */
    private $endTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"updateTimeOffRequest","readtimeoffreq"})
     */
    private $Status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="timeOffRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessId;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups({"readtimeoffreq"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allDay=false;



    public function __construct()
    {
        $this->timeoffLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userID;
    }

    public function setUserId(?User $userID): self
    {
        $this->userID = $userID;

        return $this;
    }

    public function getUserCreatorId(): ?User
    {
        return $this->userCreatorId;
    }

    public function setUserCreatorId(?User $userCreatorId): self
    {
        $this->userCreatorId = $userCreatorId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPaidHour()
    {
        return $this->paidHour;
    }

    public function setPaidHour($paidHour): self
    {
        $this->paidHour = $paidHour;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection|TimeoffLog[]
     */
    public function getTimeoffLogs(): Collection
    {
        return $this->timeoffLogs;
    }

    public function addTimeoffLog(TimeoffLog $timeoffLog): self
    {
        if (!$this->timeoffLogs->contains($timeoffLog)) {
            $this->timeoffLogs[] = $timeoffLog;
            $timeoffLog->setTimeOffRequstId($this);
        }

        return $this;
    }

    public function removeTimeoffLog(TimeoffLog $timeoffLog): self
    {
        if ($this->timeoffLogs->contains($timeoffLog)) {
            $this->timeoffLogs->removeElement($timeoffLog);
            // set the owning side to null (unless already changed)
            if ($timeoffLog->getTimeOffRequstId() === $this) {
                $timeoffLog->setTimeOffRequstId(null);
            }
        }

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

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(string $Status): self
    {
        $this->Status = $Status;

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

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getType();
    }


}
