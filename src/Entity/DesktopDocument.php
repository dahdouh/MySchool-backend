<?php

namespace App\Entity;

use App\Service\FileUploader;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DesktopDocumentRepository")
 */
class DesktopDocument
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", inversedBy="desktopDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

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

    public function getStudent(): ?Member
    {
        return $this->student;
    }

    public function setStudent(?Member $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getDir(): string
    {
        // create directory upload/member/{id_member}/desktopDocument/ if it doesn't already exist
        FileUploader::createDir($this->getStudent()->getDir() . "/", "desktopDocument");

        return $this->getStudent()->getDir() . "/desktopDocument";
    }

    /**
     * @Groups("post:read")
     */
    public function getFile()
    {
        // search file
        $fileFinder = new Finder();
        $fileFinder->files()->in($this->getDir())->name($this->getId() . ".*");

        // if the file exists
        if ($fileFinder->hasResults())
        {
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
