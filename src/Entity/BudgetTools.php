<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\Budget\BudgetGenerator;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; // gedmo annotations
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Filter\DateStringFilter;
use App\Controller\Budget\BudgetPeriodForecast;


/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('BUSINESS_SUPERVISOR')"},
 *     denormalizationContext={"groups"={"budgetwrite"}},
 *     collectionOperations={
 *     "post"={"controller"=BudgetGenerator::class},
 *     "get",
 *     "budget_forecast_dashboard"={
 *              "method"="POST",
 *              "controller"=BudgetPeriodForecast::class,
 *              "path"="/budget_tools/forecast",
 *              "swagger_context"={
 *                  "summary":"show summary of forcat budget base sent date",
 *                  "parameters"={{
 *                      "name"="payload",
 *                      "in"="body",
 *                      "type"="object",
 *                      "properties"={"startTime":{"type"="string","example"="string"},"endTime"={"type":"string"},"date"={"type":"string"}}

 *                  }}}}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BudgetToolsRepository")
 * @ApiFilter(SearchFilter::class, properties={"scheduleId": "exact"})
 * @ApiFilter(DateStringFilter::class,properties={"date":"between"}),
 */
class BudgetTools
{
    const TYPES = ['positions', 'users'];


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"budgetwrite"})
     *
     */
    private $date;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Groups({"budgetwrite"})
     */
    private $total=0;

    /**
     * percentage of total for labors
     * @ORM\Column(type="string",length=255)
     * @Groups({"budgetwrite"})
     */
    private $labor=0;


    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="budgetTools")
     * @ORM\JoinColumn(nullable=false)
     */
    private $businessId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Schedule", inversedBy="budgetTools")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"budgetwrite"})
     */
    private $scheduleId;



    public function getId(): ?int
    {
        return $this->id;
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

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getLabor()
    {
        return $this->labor;
    }

    public function setLabor($labor): self
    {
        $this->labor = $labor;

        return $this;
    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function getScheduleId(): ?Schedule
    {
        return $this->scheduleId;
    }

    public function setScheduleId(?Schedule $scheduleId): self
    {
        $this->scheduleId = $scheduleId;

        return $this;
    }

}
