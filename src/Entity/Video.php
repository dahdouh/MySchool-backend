<?php

namespace App\Entity;

use App\Service\FileUploader;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Finder\Finder;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VideoRepository")
 */
class Video
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getDir(): string
    {
        // create directory upload/course/{id_course}/video/ if it doesn't already exist
        FileUploader::createDir($this->getCourse()->getDir() . "/", "video");

        return $this->getCourse()->getDir() . "/video";
    }

    public function getFile()
    {
        // search file
        $fileFinder = new Finder();
        $fileFinder->files()->in($this->getDir())->name($this->getId() . ".*");

        // if the file exists
        if ($fileFinder->hasResults()) {
            $iterator = $fileFinder->getIterator();
            $iterator->rewind();
            return $this->getDir() . "/" . $iterator->current()->getFilename();
        }

        return null;
    }

    public function getFileExtension()
    {
        $array = explode(".", $this->getFile());
        return $this->getFile() ? end($array) : null;
    }
}
