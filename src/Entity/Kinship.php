<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KinshipRepository")
 */
class Kinship
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="kinshipStudents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tutor;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Member")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTutor(): ?Member
    {
        return $this->tutor;
    }

    public function setTutor(?Member $tutor): self
    {
        $this->tutor = $tutor;

        return $this;
    }

    public function getStudent(): ?Member
    {
        return $this->student;
    }

    public function setStudent(Member $student): self
    {
        $this->student = $student;

        return $this;
    }
}
