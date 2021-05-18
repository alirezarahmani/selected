<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\BusinessRequest\GetUserRequest;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"writebusinessreq","updatebusinessreq"} },
 *     normalizationContext={"groups"={"readbusinessreq"}},
 *     itemOperations={"get", "put"={"denormalization_context"={"groups"={"updatebusinessreq"} } }, "delete"},
 *     collectionOperations={
 *          "get",
 *          "post"={"denormalization_context"={"groups"={"writebusinessreq"} } },
 *          "get_user_requests"={
 *              "method"="GET",
 *              "controller"=GetUserRequest::class,
                "path"="/business_request/self"}
 *     }
 *
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BusinessRequestRepository")
 */
class   BusinessRequest
{
    const BUSINESS_REQUEST_ACCEPTED='accepted';
    const BUSINESS_REQUEST_DENIED='denied';
    const BUSINESS_REQUEST_SUSPEND='suspend';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"readbusinessreq","acceptbusinessreq"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="businessRequests")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("readbusinessreq")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="businessRequests")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"writebusinessreq","readbusinessreq"})
     */
    private $business;

    /**
     * @ORM\Column(type="string",length=255,nullable=false)
     * @Groups({"updatebusinessreq","readbusinessreq","acceptbusinessreq"})
     *
     */
    private $status;

    /**
     * @ORM\Column(type="string",length=255)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string",length=255)
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): self
    {
        $this->business = $business;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus( $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt( $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt( $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
