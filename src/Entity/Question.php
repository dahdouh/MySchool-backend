<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $response1;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $response2;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $response3;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $response4;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $response5;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quiz", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quiz;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Choice", mappedBy="question", orphanRemoval=true)
     */
    private $choices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question", orphanRemoval=true)
     */
    private $answers;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

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

    public function getResponse1(): ?string
    {
        return $this->response1;
    }

    public function setResponse1(string $response1): self
    {
        $this->response1 = $response1;

        return $this;
    }

    public function getResponse2(): ?string
    {
        return $this->response2;
    }

    public function setResponse2(string $response2): self
    {
        $this->response2 = $response2;

        return $this;
    }

    public function getResponse3(): ?string
    {
        return $this->response3;
    }

    public function setResponse3(string $response3): self
    {
        $this->response3 = $response3;

        return $this;
    }

    public function getResponse4(): ?string
    {
        return $this->response4;
    }

    public function setResponse4(string $response4): self
    {
        $this->response4 = $response4;

        return $this;
    }

    public function getResponse5(): ?string
    {
        return $this->response5;
    }

    public function setResponse5(string $response5): self
    {
        $this->response5 = $response5;

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

    /**
     * @return Collection|Choice[]
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice): self
    {
        if (!$this->choices->contains($choice)) {
            $this->choices[] = $choice;
            $choice->setQuestion($this);
        }

        return $this;
    }

    public function removeChoice(Choice $choice): self
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
            // set the owning side to null (unless already changed)
            if ($choice->getQuestion() === $this) {
                $choice->setQuestion(null);
            }
        }

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
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }
}
