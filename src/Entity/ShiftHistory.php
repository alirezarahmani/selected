<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ShiftHistoryRepository")
 */
class ShiftHistory
{
    const SHIFT_CREATE='create';
    const SHIFT_UPDATE='update';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shift", inversedBy="shiftHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shift_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"shiftread"})
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shiftread"})
     */
    private $type;


    /**
     * @ORM\Column(type="text",nullable=true)
     * @Groups({"shiftread"})
     */
    private $changed_property;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shiftread"})
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function getShiftId(): ?Shift
//    {
//        return $this->shift_id;
//    }

    public function setShiftId(?Shift $shift_id): self
    {
        $this->shift_id = $shift_id;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

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


    public function getChangedProperty(): ?string
    {
        return $this->changed_property;
    }

    public function setChangedProperty(string $changed_property): self
    {
        $this->changed_property = $changed_property;

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
}
