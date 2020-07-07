<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Choice", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $choice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\QuizAttempt", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $QuizAttempt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getChoice(): ?Choice
    {
        return $this->choice;
    }

    public function setChoice(?Choice $choice): self
    {
        $this->choice = $choice;

        return $this;
    }

    public function getQuizAttempt(): ?QuizAttempt
    {
        return $this->QuizAttempt;
    }

    public function setQuizAttempt(?QuizAttempt $QuizAttempt): self
    {
        $this->QuizAttempt = $QuizAttempt;

        return $this;
    }
}
