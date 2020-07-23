<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubjectRepository")
 */
class Subject
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups("post:read")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups("post:read")
     */
    private $icon;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Course", mappedBy="subject", orphanRemoval=true)
     */
    private $courses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Topic", mappedBy="subject", orphanRemoval=true)
     */
    private $topics;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Level", mappedBy="subjects")
     */
    private $levels;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Subscription", mappedBy="subjects")
     */
    private $subscriptions;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->levels = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): self
    {
        if (!$this->topics->contains($topic)) {
            $this->topics[] = $topic;
            $topic->setSubject($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->contains($topic)) {
            $this->topics->removeElement($topic);
            // set the owning side to null (unless already changed)
            if ($topic->getSubject() === $this) {
                $topic->setSubject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $this->levels[] = $level;
            $level->addSubject($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): self
    {
        if ($this->levels->contains($level)) {
            $this->levels->removeElement($level);
            $level->removeSubject($this);
        }

        return $this;
    }

    /**
     * @return Collection|Course[]
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses[] = $course;
            $course->setSubject($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->contains($course)) {
            $this->courses->removeElement($course);
            // set the owning side to null (unless already changed)
            if ($course->getSubject() === $this) {
                $course->setSubject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->addSubject($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            $subscription->removeSubject($this);
        }

        return $this;
    }
}
