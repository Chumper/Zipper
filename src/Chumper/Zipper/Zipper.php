<?php namespace Chumper\Zipper;


use Chumper\Zipper\Repositories\RepositoryInterface;
use Exception;
use Illuminate\Filesystem\Filesystem;

/**
 * This Zipper class is a wrapper around the ZipArchive methods with some handy functions
 *
 * Class Zipper
 * @package Chumper\Zipper
 */
class Zipper
{

    /**
     * Constant for extracting
     */
    const WHITELIST = 1;

    /**
     * Constant for extracting
     */
    const BLACKLIST = 2;

    /**
     * @var string Represents the current location in the zip
     */
    private $currentFolder = '';

    /**
     * @var integer The status of the Zip archive
     */
    private $status;

    /**
     * @var Filesystem Handler to the file system
     */
    private $file;

    /**
     * @var RepositoryInterface Handler to the archive
     */
    private $repository;

    /**
     * @var string The path to the current zip file
     */
    private $filePath;

    /**
     * Constructor
     *
     * @param Filesystem $fs
     */
    function __construct(Filesystem $fs = null)
    {
        $this->file = $fs ? $fs : new Filesystem();
    }

    /**
     * Create a new zip Archive if the file does not exists
     * opens a zip archive if the file exists
     *
     * @param $pathToFile string The file to open
     * @param RepositoryInterface|string $type The type of the archive, defaults to zip, possible are zip, phar, rar
     *
     * @return $this Zipper instance
     */
    public function make($pathToFile, $type = 'zip')
    {
        $this->createArchiveFile($pathToFile);
        $this->filePath = $pathToFile;

        if (is_subclass_of($type, 'Chumper\Zipper\Repositories\RepositoryInterface'))
            $this->repository = $type;
        else {
            $name = 'Chumper\Zipper\Repositories\\' . ucwords($type) . 'Repository';
            $this->repository = new $name($pathToFile);
        }

        return $this;
    }

    public function zip($pathToFile)
    {
        $this->make($pathToFile);
        return $this;
    }

    public function phar($pathToFile)
    {
        $this->make($pathToFile, 'phar');
        return $this;
    }

    public function rar($pathToFile)
    {
        $this->make($pathToFile, 'rar');
        return $this;
    }

    /**
     * Extracts the opened zip archive to the specified location <br/>
     * you can provide an array of files and folders and define if they should be a white list
     * or a black list to extract.
     *
     * @param $path string The path to extract to
     * @param array $files An array of files
     * @param int $method The Method the files should be treated
     */
    public function extractTo($path, array $files = array(), $method = Zipper::BLACKLIST)
    {
        $path = realpath($path);
        if (!$this->file->exists($path))
            $this->file->makeDirectory($path, 0755, true);

        if ($method == Zipper::WHITELIST)
            $this->extractWithWhiteList($path, $files);
        else
            $this->extractWithBlackList($path, $files);
    }

    /**
     * Gets the content of a single file if available
     *
     * @param $filePath string The full path (including all folders) of the file in the zip
     * @throws \Exception
     * @return mixed returns the content or throws an exception
     */
    public function getFileContent($filePath)
    {

        if ($this->repository->fileExists($filePath) === false)
            throw new Exception(sprintf('The file "%s" cannot be found', $filePath));

        return $this->repository->getFileContent($filePath);
    }

    /**
     * Add one or multiple files to the zip.
     *
     * @param $pathToAdd array|string An array or string of files and folders to add
     * @return $this Zipper instance
     */
    public function add($pathToAdd)
    {
        //check if array or string
        if (is_array($pathToAdd)) {
            foreach ($pathToAdd as $dir) {
                $this->add($dir);
            }
        } else if ($this->file->isFile($pathToAdd)) {
            $this->addFile($pathToAdd);
        } else
            $this->addDir($pathToAdd);

        return $this;
    }

    /**
     * Gets the status of the zip.
     *
     * @return integer The status of the internal zip file
     */
    public function getStatus()
    {
        return $this->repository->getStatus();
    }

    /**
     * Remove a file or array of files and folders from the zip archive
     *
     * @param $fileToRemove array|string The path/array to the files in the zip
     * @return $this Zipper instance
     */
    public function remove($fileToRemove)
    {
        if (is_array($fileToRemove)) {
            $this->repository->each(function ($file) use ($fileToRemove) {
                if (starts_with($file, $fileToRemove)) {
                    $this->repository->removeFile($file);
                }
            });
        } else
            $this->repository->removeFile($fileToRemove);

        return $this;
    }

    /**
     * Returns the path of the current zip file if there is one.
     * @return string The path to the file
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Closes the zip file and frees all handles
     */
    public function close()
    {
        @$this->repository->close();
        $this->filePath = "";
    }

    /**
     * Sets the internal folder to the given path.<br/>
     * Useful for extracting only a segment of a zip file.
     * @param $path
     * @return $this
     */
    public function folder($path)
    {
        $this->currentFolder = $path;
        return $this;
    }

    /**
     * Resets the internal folder to the root of the zip file.
     *
     * @return $this
     */
    public function home()
    {
        $this->currentFolder = '';
        return $this;
    }

    /**
     *
     */
    public function delete()
    {
        @$this->zip->close();
        $this->file->delete($this->filePath);
        $this->filePath = "";
    }

    public function getArchiveType()
    {
        return get_class($this->repository);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        @$this->zip->close();
    }

    //---------------------PRIVATE FUNCTIONS-------------

    /**
     * @param $pathToZip
     * @return bool
     * @throws \Exception
     */
    private function createArchiveFile($pathToZip)
    {

        if (!$this->file->exists($pathToZip)) {
            if (!$this->file->exists(dirname($pathToZip)))
                $this->file->makeDirectory(dirname($pathToZip), 0755, true);

            if (!$this->file->isWritable(dirname($pathToZip)))
                throw new Exception(sprintf('The path "%s" is not writeable', $pathToZip));

            return true;
        }
        return false;
    }

    /**
     * @param $pathToDir
     */
    private function addDir($pathToDir)
    {
        //is a dir, so remotely go through it and call the add method
        foreach ($this->file->allFiles($pathToDir) as $file) {
            $this->addFile($pathToDir . '/' . $file->getRelativePathname());
        }
    }

    /**
     * Add the file to the zip
     *
     * @param $pathToAdd
     */
    private function addFile($pathToAdd)
    {
        $info = pathinfo($pathToAdd);

        $file_name = isset($info['extension']) ?
            $info['filename'] . '.' . $info['extension'] :
            $info['filename'];

        $this->repository->addFile($pathToAdd, $this->getInternalPath() . $file_name);
    }

    /**
     * @param $path
     * @param $filesArray
     * @throws \Exception
     */
    private function extractWithBlackList($path, $filesArray)
    {

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            //check if folder
            $stats = $this->zip->statIndex($i);
            if ($stats['size'] == 0 && $stats['crc'] == 0)
                continue;

            $fileName = $this->zip->getNameIndex($i);
            $oriName = $fileName;

            if (!empty($this->currentFolder) && !starts_with($fileName, $this->currentFolder))
                continue;

            if (!empty($this->currentFolder)) {
                $fileName = str_replace($this->currentFolder . '/', '', $fileName);
            }

            if (starts_with($fileName, $filesArray)) {
                //ignore the file
                continue;
            }
            //if we are here extract it
            //get right filename
            if (!empty($this->currentFolder)) {
                $tmpPath = str_replace($this->currentFolder . '/', '', $fileName);
                $this->file->put($path . '/' . $tmpPath, $this->zip->getStream($oriName));
            } else
                if (!$this->zip->extractTo($path, $oriName))
                    throw new Exception(sprintf('The file "%s" could not be extracted to "%s"',
                        $oriName, $path));
        }
    }

    /**
     * @param $path
     * @param $filesArray
     * @throws \Exception
     */
    private function extractWithWhiteList($path, $filesArray)
    {
        $this->repository->each(function ($fileName) use ($path, $filesArray) {
            $oriName = $fileName;

            if (!empty($this->currentFolder) && !starts_with($fileName, $this->currentFolder))
                return;

            if (starts_with($this->getInternalPath() . $fileName, $filesArray)) {
                $tmpPath = str_replace($this->getInternalPath(), '', $fileName);
                $this->file->put($path . '/' . $tmpPath, $this->repository->getFileStream($oriName));
            }
        });
    }

    private function getInternalPath()
    {
        return empty($this->currentFolder) ? '' : $this->currentFolder . '/';
    }

    public function getCurrentFolderPath()
    {
        return $this->currentFolder;
    }

    public function contains($fileInArchive)
    {
        return $this->repository->fileExists($fileInArchive);
    }
}