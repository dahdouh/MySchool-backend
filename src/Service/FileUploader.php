<?php


namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /** @var UploadedFile|string $file */
    private $file;
    /** @var string $fileName */
    private $fileName;
    /** @var string $dirPath    path to directory */
    private $dirPath;
    /** @var string $dirName    target directory name */
    private $dirName;
    /** @var string $path       path + target directory name */
    private $path;
    /** @var array $path        an array of valid extensions */
    private $validExtensions;

    public function __construct($file, string $fileName, string $dirPath, string $dirName, array $validExtensions)
    {
        $this->file = $file;
        $this->fileName = $fileName;
        $this->dirPath = $dirPath;
        $this->dirName = $dirName;
        $this->path = $dirPath . "/" . $dirName;
        $this->validExtensions = $validExtensions;
    }

    /**
     * Get the type of $file is being uploaded
     * @return string "url" or "file"
     */
    public function getTypeUpload()
    {
        if (is_string($this->file)) {
            return "url";
        } else {
            return "file";
        }

    }

    /**
     * Get extension from url
     * @return string
     */
    public function getExtFromUrl()
    {
        $type = get_headers($this->file, 1)['Content-Type'];

        if (is_array($type)) {
            $type = $type[1];
        }

        return str_replace('image/', '.', $type);
    }

    /**
     * Get extension from file
     * @return string
     */
    public function getExtFromFile()
    {
        return "." . $this->file->guessExtension();
    }

    /**
     * Get extension from any file
     * @return string
     */
    public function getExt()
    {
        if ($this->getTypeUpload() == "url") {
            return $this->getExtFromUrl();
        } else {
            return $this->getExtFromFile();
        }
    }

    /**
     * Checks if the extension is valid
     * @return bool
     */
    public function isValidExt()
    {
        return in_array($this->getExt(), $this->validExtensions);
    }

    /**
     * Checks if the directory target exists
     * @param string $dirPath   path to directory target
     * @param string $dirName  directory target
     *
     * @return bool
     */
    public static function dirExists(string $dirPath, string $dirName)
    {
        $dirFinder = new Finder();
        $dirFinder->directories()->in($dirPath)->name(strval($dirName));

        return $dirFinder->hasResults();
    }

    /**
     * Create the directory target if it doesn't exist
     * @param string $dirPath   path to directory target
     * @param string $dirName   directory target
     */
    public static function createDir(string $dirPath, string $dirName)
    {
        if (!self::dirExists($dirPath, $dirName)) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($dirPath . "/" . $dirName, 0777);
        }
    }

    /**
     * Remove the directory target if it exists
     * @param string $dirPath   path to directory target
     * @param string $dirName   directory target
     */
    public static function removeDir(string $dirPath, string $dirName)
    {
        if (self::dirExists($dirPath, $dirName)) {
            $filesystem = new Filesystem();
            $filesystem->remove($dirPath . "/" . $dirName);
        }
    }

    /**
     * Remove a file from a specific directory
     * @param string $dir
     * @param string $name
     */
    public static function removeFile(string $dir, string $name)
    {
        $fileFinder = new Finder();
        $fileFinder->files()->in($dir)->name($name . ".*");

        // if the file exists
        if ($fileFinder->hasResults()) {
            $iterator = $fileFinder->getIterator();
            $iterator->rewind();

            $filesystem = new Filesystem();
            $filesystem->remove($dir . "/" . $iterator->current()->getFilename()); // delete it
        }
    }

    /**
     * Upload a file
     */
    public function uploadByFile()
    {
        $this->file->move($this->path, $this->fileName . $this->getExt());
    }

    /**
     * Upload a file from url
     */
    public function uploadByUrl()
    {
        $content = file_get_contents($this->file);
        $file =	$this->path . "/" . $this->fileName . $this->getExt();
        file_put_contents($file, $content);
    }

    /**
     * Returns 0 if there's no error, else returns the error message
     * @return int|string
     */
    public function getError()
    {
        if ($this->getTypeUpload() == "file") {
            if (!$this->file->isValid()) {
                return $this->file->getErrorMessage();
            } elseif (!$this->isValidExt()) {
                return "L'extension du fichier ({$this->getExt()}) est invalide. <br/>
                Voici les extensions autorisées : " . implode(', ', $this->validExtensions);
            }
        }

        return 0; // no error
    }

    /**
     * Upload any file
     * @return int|string
     */
    public function upload()
    {
        if ($this->getError() === 0) {
            if ($this->isValidExt()) {
                self::createDir($this->dirPath, $this->dirName);
                self::removeFile($this->path, $this->fileName);

                if ($this->getTypeUpload() == "url") {
                    $this->uploadByUrl();
                } else {
                    $this->uploadByFile();
                }
                return 0;
            }
        }

        return $this->getError();

    }
}