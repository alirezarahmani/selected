<?php
//THIS CLASS DESCRIBE SHIFT THAT SHOULD BE SENT TO USER IN EACH CRON
namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MailsToSendRepository")
 */
class MailsToSend
{
    const STATUS=['prepend','fail','sent'];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $receiverFirstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $receiverLastName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAT;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $receiverEmail;

    /**
     * @ORM\Column(type="string",length=32)
     * @Assert\Choice(choices=MailsToSend::STATUS)
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Shift")
     */
    private $shiftsInMail;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shift")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parentShift;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $prependText;

    public function __construct()
    {
        $this->shiftsInMail = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceiverFirstName(): ?string
    {
        return $this->receiverFirstName;
    }

    public function setReceiverFirstName(string $receiverFirstName): self
    {
        $this->receiverFirstName = $receiverFirstName;

        return $this;
    }

    public function getReceiverLastName(): ?string
    {
        return $this->receiverLastName;
    }

    public function setReceiverLastName(string $receiverLastName): self
    {
        $this->receiverLastName = $receiverLastName;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAT(): ?\DateTimeInterface
    {
        return $this->createdAT;
    }

    public function setCreatedAT(\DateTimeInterface $createdAT): self
    {
        $this->createdAT = $createdAT;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getReceiverEmail(): ?string
    {
        return $this->receiverEmail;
    }

    public function setReceiverEmail(string $receiverEmail): self
    {
        $this->receiverEmail = $receiverEmail;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Shift[]
     */
    public function getShiftsInMail(): Collection
    {
        return $this->shiftsInMail;
    }

    public function addShiftsInMail(Shift $shiftsInMail): self
    {
        if (!$this->shiftsInMail->contains($shiftsInMail)) {
            $this->shiftsInMail[] = $shiftsInMail;
        }

        return $this;
    }

    public function removeShiftsInMail(Shift $shiftsInMail): self
    {
        if ($this->shiftsInMail->contains($shiftsInMail)) {
            $this->shiftsInMail->removeElement($shiftsInMail);
        }

        return $this;
    }

    public function getParentShift(): ?Shift
    {
        return $this->parentShift;
    }

    public function setParentShift(?Shift $parentShift): self
    {
        $this->parentShift = $parentShift;

        return $this;
    }

    public function __toString()
    {

        return $this->receiverEmail;
    }

    public function getPrependText(): ?string
    {
        return $this->prependText;
    }

    public function setPrependText(?string $prependText): self
    {
        $this->prependText = $prependText;

        return $this;
    }

}
