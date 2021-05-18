<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
/*when ever a notification send for any user this notification also save in this table in future */
/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\NotificationHistoryRepository")
 */
class NotificationHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notificationHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $objectable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMobile=false;

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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }



    public function getObjectable()
    {
        return $this->objectable;
    }

    public function setObjectable( $objectable): self
    {
        $this->objectable = $objectable;

        return $this;
    }

    public function getIsMobile(): ?bool
    {
        return $this->isMobile;
    }

    public function setIsMobile(bool $isMobile): self
    {
        $this->isMobile = $isMobile;

        return $this;
    }
}
