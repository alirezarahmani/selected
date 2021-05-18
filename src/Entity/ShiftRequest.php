<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\ShiftRequest\ShiftRequestStatus;
//shift request has not delete they just can canceled
/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"write_request","shiftrequestedit"}},
 *     normalizationContext={"groups"={"read_request"}},
 *     itemOperations={"get","put"={"denormalization_context"={"groups"={"shiftrequestedit"} } } },
 *     collectionOperations={"get",
 *     "post"={"denormalization_context"={"groups"={"write_request"} }, "swagger_context"={ "summary":"create a request for drop swap and replace shift,**note** swap, replace, drop are valid type"} },
 *     "shift_request_status":{"method"="get","path"="/shift_requests/stattus" ,"controller"=ShiftRequestStatus::class} }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ShiftRequestRepository")
 */
class ShiftRequest
{
    //shift request status
    const SHIFT_STATUS=['accept','denied','approve','cancel','pendingAccept','decline'];

    //shift request type
    const SWAP='swap';
    const REPLACE='replace';
    const Drop='drop';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read_request","shiftread"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shiftrequestedit"})
     * @Assert\Choice(choices=ShiftRequest::SHIFT_STATUS, message="Choose a valid status.")
     * @Groups({"read_request","shiftread"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write_request","read_request"})
     * @Assert\Choice({"swap", "replace","drop"})
     */
    private $type;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShiftRequestLog", mappedBy="shiftRequestId", orphanRemoval=true)
     * @Groups({"read_request"})
     */
    private $shiftRequestLogs;



    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read_request"})
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shiftRequest")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_request"})
     */
    private $requesterId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shift", inversedBy="asRequesterShiftToRequest")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"write_request","read_request"})
     */
    private $requesterShift;


    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"write_request"})
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SwapUserShiftAccept", mappedBy="shiftRequest", orphanRemoval=true,cascade={"all"})
     * @Groups({"write_request","read_request","shiftrequestedit"})
     */
    private $swaps;



    public function __construct()
    {
        $this->shiftRequestLogs = new ArrayCollection();
        $this->swaps = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    /**
     * @return Collection|ShiftRequestLog[]
     */
    public function getShiftRequestLogs(): Collection
    {
        return $this->shiftRequestLogs;
    }

    public function addShiftRequestLog(ShiftRequestLog $shiftRequestLog): self
    {
        if (!$this->shiftRequestLogs->contains($shiftRequestLog)) {
            $this->shiftRequestLogs[] = $shiftRequestLog;
            $shiftRequestLog->setShiftRequestId($this);
        }

        return $this;
    }

    public function removeShiftRequestLog(ShiftRequestLog $shiftRequestLog): self
    {
        if ($this->shiftRequestLogs->contains($shiftRequestLog)) {
            $this->shiftRequestLogs->removeElement($shiftRequestLog);
            // set the owning side to null (unless already changed)
            if ($shiftRequestLog->getShiftRequestId() === $this) {
                $shiftRequestLog->setShiftRequestId(null);
            }
        }

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

    public function getRequesterId(): ?User
    {
        return $this->requesterId;
    }

    public function setRequesterId(?User $requesterId): self
    {
        $this->requesterId = $requesterId;

        return $this;
    }

    public function getRequesterShift(): ?Shift
    {
        return $this->requesterShift;
    }

    public function setRequesterShift(?Shift $requesterShift): self
    {
        $this->requesterShift = $requesterShift;

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

    public function getBusinessId(): ?Business
    {
        return $this->businessId;
    }

    public function setBusinessId(?Business $businessId): self
    {
        $this->businessId = $businessId;

        return $this;
    }

    /**
     * @return Collection|SwapUserShiftAccept[]
     */
    public function getSwaps(): Collection
    {
        return $this->swaps;
    }

    public function addSwap(SwapUserShiftAccept $swap): self
    {
        if (!$this->swaps->contains($swap)) {
            $this->swaps[] = $swap;
            $swap->setShiftRequest($this);
        }

        return $this;
    }

    public function removeSwap(SwapUserShiftAccept $swap): self
    {
        if ($this->swaps->contains($swap)) {
            $this->swaps->removeElement($swap);
            // set the owning side to null (unless already changed)
            if ($swap->getShiftRequest() === $this) {
                $swap->setShiftRequest(null);
            }
        }

        return $this;
    }


}
