<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TopicRepository")
 */
class Topic
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
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups("post:read")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Subject", inversedBy="topics")
     * @ORM\JoinColumn(nullable=false, name="subject")
     * @Groups("post:read")
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="topics")
     * @ORM\JoinColumn(nullable=false, name="author")
     * @Groups("post:read")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Forum", inversedBy="topics")
     * @ORM\JoinColumn(nullable=false, name="forum")
     */
    private $forum;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="topic", orphanRemoval=true)
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getAuthor(): ?Member
    {
        return $this->author;
    }

    public function setAuthor(?Member $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): self
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setTopic($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getTopic() === $this) {
                $post->setTopic(null);
            }
        }

        return $this;
    }
}
