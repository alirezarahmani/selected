<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('BUSINESS_EMPLOYEE')"},
 *     denormalizationContext={"groups"={"schwrite"}},
 *     normalizationContext={"groups"={"userread","schread","readjobsites","shifttemplateread"}},
 *     collectionOperations={"get"={"path"="/industries"},
 *     "post"
 *     })
 *
 * @ORM\Entity(repositoryClass="App\Repository\IndustryRepository")
 */
class Industry
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"schwrite","schread","userread","readjobsites","read_attendance_times","annotation_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"schwrite","userread","schread","readjobsites","shifttemplateread","annotation_read","shiftread","read_request","read_attendance_times"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"schwrite","schread","read_attendance_times"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Business", inversedBy="industries")
     * @ORM\JoinColumn(nullable=null)
     */
    private $businessId;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="userHasIndustries")
     */
    private $users;


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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


    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addUserHasSchedule($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeUserHasSchedule($this);
        }

        return $this;
    }

}
