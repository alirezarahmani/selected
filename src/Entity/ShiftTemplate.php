<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     attributes={"filters"={"shiftTemplate.search_filter"}},
 *     denormalizationContext={"groups"={"shifttemplatewrite"}},
 *     normalizationContext={"groups"={"shifttemplateread"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"scheduleId": "exact","positionId": "exact"})
 * @ORM\Entity(repositoryClass="App\Repository\ShiftTemplateRepository")
 */
class ShiftTemplate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"shifttemplateread"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Position", inversedBy="shiftTemplates")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $positionId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schedule", inversedBy="shiftTemplates")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $scheduleId;

    /**
     * @ORM\Column(type="string")
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $startTime;

    /**
     * @ORM\Column(type="string")
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $endTime;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $notes;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"shifttemplatewrite","shifttemplateread"})
     */
    private $unpaidBreak=0;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPositionId(): ?Position
    {
        return $this->positionId;
    }

    public function setPositionId(?Position $positionId): self
    {
        $this->positionId = $positionId;

        return $this;
    }

    public function getScheduleId(): ?Schedule
    {
        return $this->scheduleId;
    }

    public function setScheduleId(?Schedule $scheduleId): self
    {
        $this->scheduleId = $scheduleId;

        return $this;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setStartTime( $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function setEndTime( $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getUnpaidBreak(): ?int
    {
        return $this->unpaidBreak;
    }

    public function setUnpaidBreak(int $unpaidBreak): self
    {
        $this->unpaidBreak = $unpaidBreak;

        return $this;
    }





}
