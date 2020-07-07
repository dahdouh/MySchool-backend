<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuizAttemptRepository")
 */
class QuizAttempt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="quizAttempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quiz", inversedBy="quizAttempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quiz;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="float")
     */
    private $score = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="QuizAttempt", orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isComplete = false;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

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

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuizAttempt($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuizAttempt() === $this) {
                $answer->setQuizAttempt(null);
            }
        }

        return $this;
    }

    public function getIsComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function setIsComplete(bool $isComplete): self
    {
        $this->isComplete = $isComplete;

        return $this;
    }
}
