<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * /@ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{

    public function __construct()
    {
        $this->outingsSubscribed = new ArrayCollection();
        $this->outingsOrganized = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique = true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $admin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Campus")
     */
    private $campus;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Outing", inversedBy="participants")
     */
    private $outingsSubscribed;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\Outing", mappedBy="organizer")
     */
    private $outingsOrganized;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCampus()
    {
        return $this -> campus;
    }

    /**
     * @param mixed $campus
     */
    public function setCampus($campus): void
    {
        $this -> campus = $campus;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

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

    /**
     * @return Collection|Outing[]
     */
    public function getOutingsSubscribed(): Collection
    {
        return $this->outingsSubscribed;
    }

    public function addOutingSubscribed(Outing  $outingSubscribed):self
    {
        if(!$this->outingsSubscribed->contains($outingSubscribed)){
            $this->outingsSubscribed[] = $outingSubscribed;
        }
        return $this;
    }
    /**
     * @param Collection $outingsSubscribed
     */
    public function setOutingsSubscribed(Collection $outingsSubscribed): self
    {
        $this->outingsSubscribed = $outingsSubscribed;
    }

    /**
     * @return ArrayCollection
     */
    public function getOutingsOrganized(): ArrayCollection
    {
        return $this->outingsOrganized;
    }

    /**
     * @param ArrayCollection $outingsOrganized
     */
    public function setOutingsOrganized(ArrayCollection $outingsOrganized): void
    {
        $this->outingsOrganized = $outingsOrganized;
    }



    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }
}
