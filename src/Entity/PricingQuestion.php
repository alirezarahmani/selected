<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PricingQuestionRepository;
use App\Controller\PricingQuestion\totalResultPricingQuestion;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\PricingQuestion\LastFormulaRegister;
use App\Controller\PricingQuestion\ListPricingQuestion;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"pricing_read"}},
 *     collectionOperations={
 *     "get"={"controller":ListPricingQuestion::class},
 *     "post",
 *     "last_formula"={"method"="post",
 *                     "path":"/pricing_questions/last_formula",
 *                     "controller":LastFormulaRegister::class,
 *                     "swagger_context"={
 *                              "parameters"={{"in"="body", "name"="payload", "type"="object", "properties"={"formula"={"type"="string", "example"="{/api/pricing_questions/1}*{/api/pricing_questions/2}+45*500*96"}  } }}

 *                      }
 *      },
 *
 *     "calculate_answer"={"method"="post",
 *                          "path":"/pricing_questions/total_all_question",
 *                          "controller"=totalResultPricingQuestion::class,
 *                          "swagger_context"={
 *                                "parameters"={{"in"="body","name"="payload","type"="object","properties"={"answers"={"example"={"/api/pricing_questions/1":"5"} } }  }}

 *                       },
 *      }
 *
 * })
 * @ORM\Entity(repositoryClass=PricingQuestionRepository::class)
 */
class PricingQuestion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"pricing_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"pricing_read"})
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"pricing_read"})
     */
    private $media;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"pricing_read"})
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     * @Groups({"pricing_read"})
     * @ApiProperty(swaggerContext={"desctiption"="each formula should have only one variable","example"="x*356"})
     */
    private $formula;
    /**
     *  @Groups({"pricing_read"})
     */

    private $answer;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"pricing_read"})
     */
    private $final=false;

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param mixed $answer
     */
    public function setAnswer($answer): void
    {
        $this->answer = $answer;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFormula(): ?string
    {
        return $this->formula;
    }

    public function setFormula(string $formula): self
    {
        $this->formula = $formula;

        return $this;
    }

    public function getFinal(): ?bool
    {
        return $this->final;
    }

    public function setFinal(bool $final): self
    {
        $this->final = $final;

        return $this;
    }
}
