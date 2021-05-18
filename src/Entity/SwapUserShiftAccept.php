<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"write_request"}},
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SwapUserShiftAcceptRepository")
 */
class SwapUserShiftAccept
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="swapUserShiftAccepts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"write_request","read_request"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shift")
     * @Groups({"write_request","read_request"})
     */
    private $shift;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read_request"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ShiftRequest", inversedBy="swaps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shiftRequest;



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

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function setShift(?Shift $shift): self
    {
        $this->shift = $shift;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getShiftRequest(): ?ShiftRequest
    {
        return $this->shiftRequest;
    }

    public function setShiftRequest(?ShiftRequest $shiftRequest): self
    {
        $this->shiftRequest = $shiftRequest;

        return $this;
    }


}
