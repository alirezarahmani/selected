<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"write_allowed"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\AllowedTerminalIpRepository")
 */
class AllowedTerminalIp
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="allowedTerminalIps")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $business;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups({"write_allowed"})
     * @ApiProperty(attributes={"swagger_context"={"example":"home"} })
     */
    private $label;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"write_allowed"})
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=128)
     * @Groups({"write_allowed"})
     * @ApiProperty(attributes={"swagger_context"={"example":"127.0.0.1"} })
     * @Assert\Regex(pattern="/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/",message="valid ip range is something like 127.0.0.1")
     */
    private $ip;

    /**
     * @ORM\Column(type="text")
     * @Groups({"write_allowed"})
     * @ApiProperty(attributes={"swagger_context"={"example"="lat,lng" } })
     */
    private $location;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }
}
