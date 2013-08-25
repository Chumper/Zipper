<?php namespace Chumper\Zipper;


use Exception;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

/**
 * This Zipper class is a wrapper around the ZipArchive methods with some handy functions
 *
 * Class Zipper
 * @package Chumper\Zipper
 */
class Zipper {

    /**
     * Constant for extracting
     */
    const WHITELIST = 1;

    /**
     * Constant for extracting
     */
    const BLACKLIST = 2;

    /**
     * @var integer The status of the Zip archive
     */
    private  $status;

    /**
     * @var Filesystem Handler to the file system
     */
    private $file;

    /**
     * @var ZipArchive Handler to the zip archive
     */
    private $zip;

    /**
     * Constructor
     *
     * @param Filesystem $fs
     * @param ZipArchive $zip
     */
    function __construct(Filesystem $fs = null, ZipArchive $zip = null)
    {
        $this->file = $fs ? $fs : new Filesystem();
        $this->zip = $zip ? $zip : new ZipArchive();
    }

    /**
     * Create a new Archive if the file does not exists
     * opens an archive if the file exists
     *
     * @param $pathToFile string The file to open
     * @return $this Zipper instance
     */
    public function make($pathToFile)
    {
        $new = $this->createZipFile($pathToFile);
        $this->openFile($pathToFile, $new);
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

        if($method == Zipper::WHITELIST)
            $this->extractWithWhiteList($path,$files);
        else
            $this->extractWithBlackList($path, $files);
    }

    /**
     * Gets the content of a single file if available
     *
     * @param $filePath string The path of the file in the zip
     * @return mixed returns the content or throws an exception
     * @throws \Exception
     */
    public function getFileContent($filePath)
    {
        if($this->zip->locateName($filePath) === false)
            throw new Exception(sprintf('The file "%s" cannot be found', $filePath));

        return $this->zip->getFromName($filePath);
    }

    /**
     * Add one or multiple files to the zip.
     *
     * @param $pathToAdd array|string An array or string of files and folders to add
     * @param string $rootDirInZip The root directory in the zip. All folders will be appenderd
     * @return $this Zipper instance
     */
    public function add($pathToAdd, $rootDirInZip = '')
    {
        //check if array or string
        if(is_array($pathToAdd))
        {
            foreach($pathToAdd as $dir)
            {
                $this->add($dir, $rootDirInZip);
            }
        }
        else if($this->file->isFile($pathToAdd))
        {
            $this->addFile($pathToAdd,$rootDirInZip);
        }
        else
            $this->addDir($pathToAdd,$rootDirInZip);

        return $this;
    }

    /**
     * Gets the status of the zip.
     *
     * @return integer The status of the internal zip file
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Remove a file or array of files and folders from the zip archive
     *
     * @param $fileToRemove array|string The path/array to the files in the zip
     * @return $this Zipper instance
     */
    public function remove($fileToRemove)
    {
        if(is_array($fileToRemove))
        {
            for($i=0; $i<$this->zip->numFiles; $i++)
            {
                $fileName = $this->zip->getNameIndex($i);
                if(starts_with($fileName,$fileToRemove))
                {
                    //remove
                    $this->zip->deleteIndex($fileName);
                }
            }
        }
        else
            $this->zip->deleteName($fileToRemove);

        return $this;
    }

    /**
     * Closes the zip file and frees all handles
     */
    public function close()
    {
        @$this->zip->close();
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
    private function createZipFile($pathToZip){

        if(!$this->file->exists($pathToZip))
        {
            if(!$this->file->exists(dirname($pathToZip)))
               $this->file->makeDirectory(dirname($pathToZip),0755,true);

            if(!$this->file->isWritable(dirname($pathToZip)))
                throw new Exception(sprintf('The path "%s" is not writeable',$pathToZip));

            return true;
        }
        return false;
    }

    /**
     * @param $pathToFile
     * @param bool $create
     */
    private function openFile($pathToFile, $create = false)
    {
        if($create)
            $this->status = $this->zip->open($pathToFile, ZipArchive::CREATE);
        else
            $this->status = $this->zip->open($pathToFile);
    }

    /**
     * @param $pathToDir
     * @param $rootDirInZip
     */
    private function addDir($pathToDir, $rootDirInZip)
    {
        //is a dir, so remotely go through it and call the add method
        foreach($this->file->allFiles($pathToDir) as $file)
        {
            $this->addFile($pathToDir.'/'.$file->getRelativePathname(),$rootDirInZip);
        }
    }

    /**
     * @param $pathToAdd
     * @param $rootDirInZip
     */
    private function addFile($pathToAdd, $rootDirInZip)
    {
        empty($rootDirInZip) ? $path = false : $path = true;

        if(!$path)
            $this->zip->addFile($pathToAdd);
        else
            $this->zip->addFile($pathToAdd,$rootDirInZip.'/'.$pathToAdd);
    }

    /**
     * @param $path
     * @param $filesArray
     * @throws \Exception
     */
    private function extractWithBlackList($path, $filesArray)
    {
        for($i=0; $i<$this->zip->numFiles; $i++)
        {
            $fileName = $this->zip->getNameIndex($i);
            if(starts_with($fileName,$filesArray))
            {
                //ignore the file
                continue;
            }
            //if we are here extract it

            if(!$this->zip->extractTo($path,$fileName))
                throw new Exception(sprintf('The file "%s" could not be extracted to "%s"',
                    $fileName, $path));
        }
    }

    /**
     * @param $path
     * @param $filesArray
     * @throws \Exception
     */
    private function extractWithWhiteList($path, $filesArray)
    {
        for($i=0; $i<$this->zip->numFiles; $i++)
        {
            $fileName = $this->zip->getNameIndex($i);
            if(starts_with($fileName,$filesArray))
            {
                if(!$this->zip->extractTo($path,$fileName))
                    throw new Exception(sprintf('The file "%s" could not be extracted to "%s"',
                        $fileName, $path));
            }
        }
    }
}