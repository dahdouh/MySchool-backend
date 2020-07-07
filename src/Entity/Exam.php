<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExamRepository")
 */
class Exam
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="exams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

    /**
     * @ORM\Column(type="text")
     */
    private $correction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getCorrection(): ?string
    {
        return $this->correction;
    }

    public function setCorrection(string $correction): self
    {
        $this->correction = $correction;

        return $this;
    }
}
