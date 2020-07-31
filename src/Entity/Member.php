<?php

namespace App\Entity;

use App\Service\FileUploader;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 */
class Member implements UserInterface
{
    // Role Types
    static $ROLE_ADMIN = [
        "badge" => "badge-admin",
        "name" => "Administrateur",
        "code" => "ROLE_ADMIN",
        "isAdmin" => true
    ];

    static $ROLE_STUDENT = [
        "badge" => "badge-student",
        "name" => "Étudiant",
        "code" => "ROLE_STUDENT"
    ];

    static $ROLE_TEACHER = [
        "badge" => "badge-teacher",
        "name" => "Enseignant",
        "code" => "ROLE_TEACHER"
    ];

    static $ROLE_TUTOR = [
        "badge" => "badge-tutor",
        "name" => "Parent",
        "code" => "ROLE_TUTOR"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=false)
     * @Groups("post:read")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180, unique=false, nullable=true)
     * @Groups("post:read")
     */
    private $tel;

    /**
     * @ORM\Column(type="json")
     * @Groups("post:read")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $lastName;


    /**
     * @ORM\Column(type="string")
     * @Assert\File(maxSize = "23M",
     *     mimeTypes = {"image/jpeg", "image/jpg", "image/gif", "image/png"},)
     * @Groups("post:read")
     */
    private $image;

    /**
     * @ORM\Column(type="boolean")
     *
     */
    private $isActive = true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Kinship", mappedBy="tutor", orphanRemoval=true)
     * @Groups("post:read")
     */
    private $kinshipStudents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Topic", mappedBy="author", orphanRemoval=true)
     */
    private $topics;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author", orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $dateRegistration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="student")
     * @Groups("post:read")
     */
    private $subscriptions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Course", mappedBy="teacher")
     */
    private $courses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="student")
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="student", orphanRemoval=true)
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuizAttempt", mappedBy="student")
     */
    private $quizAttempts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DesktopDocument", mappedBy="student")
     * @Groups("post:read")
     */
    private $desktopDocuments;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("post:read")
     */
    private $date_birth;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\HistoricAction", mappedBy="actor", orphanRemoval=true)
     */
    private $actions;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->kinshipStudents = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->quizAttempts = new ArrayCollection();
        $this->desktopDocuments = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->kinshipStudents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Get all existing role types
     *
     * @return array
     */
    public static function getAllRoleTypes(): array
    {
        return [self::$ROLE_ADMIN, self::$ROLE_STUDENT, self::$ROLE_TEACHER, self::$ROLE_TUTOR];
    }

    /**
     * Get all existing role types except ADMIN_ROLE
     *
     * @return array
     */
    public static function getRoleTypesExceptAdmin(): array
    {
        return [self::$ROLE_STUDENT, self::$ROLE_TEACHER, self::$ROLE_TUTOR];
    }

    /**
     * Tests if the user has a specific role
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_MEMBER
        $roles[] = 'ROLE_MEMBER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    /**
     * @return Collection|Member[]
     */
    public function getStudents(): Collection
    {
        $students = new ArrayCollection();
        /** @var Kinship $kinshipStudent */
        foreach ($this->kinshipStudents as $kinshipStudent) {
            $students->add($kinshipStudent->getStudent());
        }
        return $students;
    }

    public function getDir(): string
    {
        // create directory upload/member/ if it doesn't already exist
        FileUploader::createDir("upload/", "member");
        // create directory upload/member/{id_member}/ if it doesn't already exist
        FileUploader::createDir("upload/member/", $this->getId());

        return "upload/member/" . $this->getId();
    }

    /**
     * @return Collection|KinshipStudents[]
     */
    public function getKinshipStudents(): Collection
    {
        return $this->kinshipStudents;
    }

    public function addKinshipStudents(Kinship $kk): self
    {
        if (!$this->kinshipStudents->contains($kk)) {
            $this->kinshipStudents[] = $kk;
            $kinshipStudents->setAuthor($this);
        }

        return $this;
    }

    public function removeKinshipStudents(Kinship $k): self
    {
        if ($this->kinshipStudents->contains($k)) {
            $this->kinshipStudents->removeElement($k);
            // set the owning side to null (unless already changed)
            if ($kinshipStudents->getAuthor() === $this) {
                $kinshipStudents->setAuthor(null);
            }
        }

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
            $topic->setAuthor($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->contains($topic)) {
            $this->topics->removeElement($topic);
            // set the owning side to null (unless already changed)
            if ($topic->getAuthor() === $this) {
                $topic->setAuthor(null);
            }
        }

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
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    public function getDateRegistration(): ?DateTimeInterface
    {
        return $this->dateRegistration;
    }

    public function setDateRegistration(DateTimeInterface $dateRegistration): self
    {
        $this->dateRegistration = $dateRegistration;

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
            $subscription->setStudent($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getStudent() === $this) {
                $subscription->setStudent(null);
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
            $answer->setStudent($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getStudent() === $this) {
                $answer->setStudent(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setStudent($this);
        }
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getStudent() === $this) {
                $event->setStudent(null);
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
            $quizAttempt->setStudent($this);
        }

        return $this;
    }

    public function removeQuizAttempt(QuizAttempt $quizAttempt): self
    {
        if ($this->quizAttempts->contains($quizAttempt)) {
            $this->quizAttempts->removeElement($quizAttempt);
            // set the owning side to null (unless already changed)
            if ($quizAttempt->getStudent() === $this) {
                $quizAttempt->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DesktopDocument[]
     */
    public function getDesktopDocuments(): Collection
    {
        return $this->desktopDocuments;
    }

    public function getDateBirth(): ?DateTimeInterface
    {
        return $this->date_birth;
    }

    public function setDateBirth(DateTimeInterface $date_birth): self
    {
        $this->date_birth = $date_birth;

        return $this;
    }

    /**
     * @return Collection|HistoricAction[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(HistoricAction $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setActor($this);
        }

        return $this;
    }

    public function removeAction(HistoricAction $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getActor() === $this) {
                $action->setActor(null);
            }
        }

        return $this;
    }
}
