<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"shiftReqLogWrite"} },
 *     itemOperations={"get"}
 *     )
 * @ORM\Entity(repositoryClass="App\Repository\ShiftRequestLogRepository")
 */
class ShiftRequestLog
{
    //this status is when user want create a message that save as log
    const NON_TYPE='none';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ShiftRequest", inversedBy="shiftRequestLogs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"shiftReqLogWrite"})
     */
    private $shiftRequestId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false),
     * @Groups({"read_request"})
     */
    private $creatorId;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"shiftReqLogWrite","read_request"})
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_request"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_request"})
     */
    private $requestDate;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShiftRequestId(): ?ShiftRequest
    {
        return $this->shiftRequestId;
    }

    public function setShiftRequestId(?ShiftRequest $shiftRequestId): self
    {
        $this->shiftRequestId = $shiftRequestId;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRequestDate(): ?string
    {
        return $this->requestDate;
    }

    public function setRequestDate(string $requestDate): self
    {
        $this->requestDate = $requestDate;

        return $this;
    }

}
