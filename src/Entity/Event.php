<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{

    //Class available
    static $CLASS = [
        "event-important",
        "event-special",
        "event-warning",
        "event-info",
        "event-success",
        "event-inverse"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $class;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Get all existing classes
     *
     * @return array
     */
    public static function getAllClass(): array
    {
        return self::$CLASS;
    }

    /**
     * Tests if the event has a specific class
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasClass(string $class): bool
    {
        return $class == $this->getClass();
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

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
}
