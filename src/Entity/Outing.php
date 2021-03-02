<?php

namespace App\Entity;

use App\Repository\OutingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OutingRepository::class)
 */
class Outing
{
    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDateTime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="datetime")
     */
    private $entryDeadline;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxNumberEntries;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne (targetEntity="App\Entity\State")
     */
    private $state;

    /**
     * @ORM\ManyToOne  (targetEntity="App\Entity\Place")
     *
     */
    private $place;

    /**
     * @ORM\ManyToOne  (targetEntity="App\Entity\Campus")
     */
    private $campus;

    /**
     * @ORM\OneToMany (targetEntity="App\Entity\User", mappedBy="outingsSubscribed", cascade="remove")
     */
    private $participants;

    /**
     * @ORM\ManyToOne  (targetEntity="App\Entity\User", inversedBy="outingsOrganized")
     */
    private $organizer;

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

    public function getStartDateTime(): ?\DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeInterface $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getEntryDeadline(): ?\DateTimeInterface
    {
        return $this->entryDeadline;
    }

    public function setEntryDeadline(\DateTimeInterface $entryDeadline): self
    {
        $this->entryDeadline = $entryDeadline;

        return $this;
    }

    public function getMaxNumberEntries(): ?int
    {
        return $this->maxNumberEntries;
    }

    public function setMaxNumberEntries(int $maxNumberEntries): self
    {
        $this->maxNumberEntries = $maxNumberEntries;

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

    public function getState(): State
    {
        return $this->state;
    }

    public function setState(State $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param mixed $place
     */
    public function setPlace($place): void
    {
        $this->place = $place;
    }

    /**
     * @return mixed
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * @param mixed $campus
     */
    public function setCampus($campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return ArrayCollection
     */
    public function getParticipants(): ArrayCollection
    {
        return $this->participants;
    }

    /**
     * @param ArrayCollection $participants
     */
    public function setParticipants(ArrayCollection $participants): void
    {
        $this->participants = $participants;
    }

    /**
     * @return mixed
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * @param mixed $organizer
     */
    public function setOrganizer($organizer): void
    {
        $this->organizer = $organizer;
    }


}
