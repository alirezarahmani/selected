<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\Media\CreateMediaObjectAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ApiResource(
 *     collectionOperations={
 *         "post"={
 *             "controller"=CreateMediaObjectAction::class,
 *             "deserialize"=false,
 *             "validation_groups"={"Default", "media_object_create"},
 *             "denormalization_context"={"groups"={"media_object_create"}},
 *             "normalization_context"={"groups"={"media_object_read"}},
 *              "swagger_context"={
 *                   "summary":"upload file",
 *                  "consumes" = {
 *                       "multipart/form-data",
 *                    },
 *                   "parameters"={
 *                      {"name"="payload",
 *                      "in"="body",
 *                      "description": "leave it empty in ajaxes",
 *                      "properties":null
 *                      },
 *                      {"name"="file",
 *                      "in"="formData",
 *                      "type":"file"
 *                      },
 *                       {"name"="objectable",
 *                      "in"="formData",
 *                      "type":"string",
 *                      "description":"valid choice is attendance_times, billing, pricing_questions ,users ,business"
 *                      }
 *                  }
 *                }

 *         },
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     }
 * )
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass="App\Repository\MediaRepository")
 */
class Media
{
    CONST OBJECTABEL=["attendance_times","billing","pricing_questions","users","business"];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"media_object_read","read","user_business_read","pricing_read","userread"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"media_object_read","read","user_business_read","pricing_read","userread"})
     */
    private $filePath;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"media_object_read","userread"})
     */
    private $confirmed=false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"media_object_create","media_object_read"})
     * @Assert\Choice(choices=Media::OBJECTABEL)
     */
    private $objectable;

    /**
     * @var File|null
     * @Groups({"media_object_create"})
     * @Assert\NotNull(groups={"media_object_create"})
     * @Assert\File(mimeTypes={"image/*"})
     * @Vich\UploadableField(mapping="media_object", fileNameProperty="filePath")
     */
    public $file;

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @throws \Exception
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->createdAt = new \DateTimeImmutable();
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getObjectable(): ?string
    {
        return $this->objectable;
    }

    public function setObjectable(string $objectable): self
    {
        $this->objectable = $objectable;

        return $this;
    }
}
