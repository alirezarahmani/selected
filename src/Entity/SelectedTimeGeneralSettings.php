<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(collectionOperations={"get"},itemOperations={"put","get"})
 * @ORM\Entity(repositoryClass="App\Repository\SelectedTimeGeneralSettingsRepository")
 */
class SelectedTimeGeneralSettings
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @ApiProperty(swaggerContext={"description":"this value is in GBP"})
     */
    private $premiumPerUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPremiumPerUser(): ?string
    {
        return $this->premiumPerUser;
    }

    public function setPremiumPerUser(string $premiumPerUser): self
    {
        $this->premiumPerUser = $premiumPerUser;

        return $this;
    }
}
