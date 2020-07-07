<?php

namespace App\Entity;

use App\Service\CurrentMember;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HistoricActionRepository")
 */
class HistoricAction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="actions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:read")
     */
    private $actor;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Groups("post:read")
     */
    private $link;

    public function __construct(CurrentMember $currentMember)
    {
        $this->date = new DateTime();
        $this->actor = $currentMember->getMember();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActor(): ?Member
    {
        return $this->actor;
    }

    public function setActor(?Member $actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
