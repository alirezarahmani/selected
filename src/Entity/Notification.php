<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Notification\AddNotification;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"wrirte_notification"} },
 *     collectionOperations={
 *          "GET",
 *          "POST"={
 *             "controller"=AddNotification::class,
 *             "deserialize"=false,
 *             "validation_groups"={"Default", "media_object_create"},
 *             "denormalization_context"={"groups"={"media_object_create"}},
 *             "normalization_context"={"groups"={"media_object_read"}},
 *             "swagger_context"={
 *                  "summary"="user send  registeration notification token from his device and join his device to group",
 *                  "parameters"={{
 *                  "name"="payload",
 *                  "type"="object",
 *                  "in"="body",
 *                  "properties"={
 *                      "registrationtoken"={
 *                          "type"="string"
 *                      },
 *                      "business"={
 *                          "type"="string",
 *                          "example"="/api/businesses/1"
 *                      }
 *                  }
 *                   }},
 *
 *              }
 *          }
 *
 *      })
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"wrirte_notification"})
     *
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     * @Groups({"wrirte_notification"})
     * @ApiProperty(attributes={ "swagger_context"={"summary"="notification key to send to device group of user"} })
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     *
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $business;



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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

        return $this;
    }


}
