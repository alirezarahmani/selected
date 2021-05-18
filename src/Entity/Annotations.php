<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Filter\DateStringFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Annotation\AnnotationType;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"annotation_write"}},
 *     normalizationContext={"groups"={"annotation_read"}},
 *     collectionOperations={"get",
 *     "post",
 *     "get_annotation_type"={"method"="get","controller"=AnnotationType::class,"path"="/annotations/type"} }
 * )
 *  @ApiFilter(DateStringFilter::class,properties={"startDate":"after","endDate":"before"}),
 *  @ApiFilter(SearchFilter::class,properties={"scheduleId":"exact"}),
 * @ORM\Entity(repositoryClass="App\Repository\AnnotationsRepository")
 */
class Annotations
{
    //annotation types

    const ANNOTATION_TYPE=['closed','no_time_off','announcement'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"annotation_read"})
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annotation_write","annotation_read"})
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"annotation_write","annotation_read"})

     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annotation_write","annotation_read"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annotation_write","annotation_read"})

     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schedule", inversedBy="annotations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"annotation_write","annotation_read"})

     */
    private $scheduleId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="annotations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annotation_write","annotation_read"})

     */
    private $startDate;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annotation_write","annotation_read"})
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"annotation_read"})
     */
    private $createdBy;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"annotation_read"})

     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getScheduleId(): ?Schedule
    {
        return $this->scheduleId;
    }

    public function setScheduleId(?Schedule $schedule_id): self
    {
        $this->scheduleId = $schedule_id;

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

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(string $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

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
}
