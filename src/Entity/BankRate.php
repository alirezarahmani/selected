<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(collectionOperations={"get"},itemOperations={})
 * @ORM\Entity(repositoryClass="App\Repository\BankRateRepository")
 */
class BankRate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $base;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $AUD;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CAD;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $DKK;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $EUR;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $GBP;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $NZD;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $SEK;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $USD;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBase(): ?string
    {
        return $this->base;
    }

    public function setBase(string $base): self
    {
        $this->base = $base;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAUD(): ?string
    {
        return $this->AUD;
    }

    public function setAUD(string $AUD): self
    {
        $this->AUD = $AUD;

        return $this;
    }

    public function getCAD(): ?string
    {
        return $this->CAD;
    }

    public function setCAD(string $CAD): self
    {
        $this->CAD = $CAD;

        return $this;
    }

    public function getDKK(): ?string
    {
        return $this->DKK;
    }

    public function setDKK(string $DKK): self
    {
        $this->DKK = $DKK;

        return $this;
    }

    public function getEUR(): ?string
    {
        return $this->EUR;
    }

    public function setEUR(string $EUR): self
    {
        $this->EUR = $EUR;

        return $this;
    }

    public function getGBP(): ?string
    {
        return $this->GBP;
    }

    public function setGBP(string $GBP): self
    {
        $this->GBP = $GBP;

        return $this;
    }

    public function getNZD(): ?string
    {
        return $this->NZD;
    }

    public function setNZD(string $NZD): self
    {
        $this->NZD = $NZD;

        return $this;
    }

    public function getSEK(): ?string
    {
        return $this->SEK;
    }

    public function setSEK(string $SEK): self
    {
        $this->SEK = $SEK;

        return $this;
    }

    public function getUSD(): ?string
    {
        return $this->USD;
    }

    public function setUSD(string $USD): self
    {
        $this->USD = $USD;

        return $this;
    }
}
