<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $date_start;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $date_end;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("post:read")
     */
    private $isActive;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false, name="student")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Level", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false, name="level")
     * @Groups("post:read")
     */
    private $level;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Subject", inversedBy="subscriptions")
     * @Groups("post:read")
     */
    private $subjects;

    /**
     * @ORM\Column(type="float")
     * @Groups("post:read")
     */
    private $price;

    public function __construct()
    {
        $this->subjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeInterface $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(\DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getStudent(): ?Member
    {
        return $this->student;
    }

    public function setStudent(?Member $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection|Subject[]
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        if ($this->subjects->contains($subject)) {
            $this->subjects->removeElement($subject);
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
