<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuizRepository")
 */
class Quiz
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="quizzes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="quiz", orphanRemoval=true)
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuizAttempt", mappedBy="quiz", orphanRemoval=true)
     */
    private $quizAttempts;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->quizAttempts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|QuizAttempt[]
     */
    public function getQuizAttempts(): Collection
    {
        return $this->quizAttempts;
    }

    public function addQuizAttempt(QuizAttempt $quizAttempt): self
    {
        if (!$this->quizAttempts->contains($quizAttempt)) {
            $this->quizAttempts[] = $quizAttempt;
            $quizAttempt->setQuiz($this);
        }

        return $this;
    }

    public function removeQuizAttempt(QuizAttempt $quizAttempt): self
    {
        if ($this->quizAttempts->contains($quizAttempt)) {
            $this->quizAttempts->removeElement($quizAttempt);
            // set the owning side to null (unless already changed)
            if ($quizAttempt->getQuiz() === $this) {
                $quizAttempt->setQuiz(null);
            }
        }

        return $this;
    }
}
