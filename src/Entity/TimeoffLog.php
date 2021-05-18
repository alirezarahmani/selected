<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"timeOffLogWrite"} },
 *     itemOperations={"get"}
 *     )
 * @ORM\Entity(repositoryClass="App\Repository\TimeoffLogRepository")
 */
class TimeoffLog
{
    const NON_STATUS='non_status';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeOffRequest", inversedBy="timeoffLogs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"timeOffLogWrite"})
     */
    private $timeOffRequstId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readtimeoffreq"})
     */
    private $creatorId;


    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"readtimeoffreq","timeOffLogWrite"})
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"readtimeoffreq"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"readtimeoffreq"})
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeOffRequstId(): ?TimeOffRequest
    {
        return $this->timeOffRequstId;
    }

    public function setTimeOffRequstId(?TimeOffRequest $timeOffRequstId): self
    {
        $this->timeOffRequstId = $timeOffRequstId;

        return $this;
    }

    public function getCreatorId(): ?User
    {
        return $this->creatorId;
    }

    public function setCreatorId(?User $creatorId): self
    {
        $this->creatorId = $creatorId;

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

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string|null
     * time off log required for showing process changes from timeOffRequest,
     * a request has just one status at time so we need something to show us changes of it
     */

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

}
