<?php namespace Chumper\Zipper;


use Exception;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;

class Zipper {

    const WHITELIST = 1;
    const BLACKLIST = 2;

    private  $status;

    /**
     * @var Filesystem
     */
    private $file;

    /**
     * @var ZipArchive
     */
    private $zip;

    function __construct(Filesystem $fs = null, ZipArchive $zip = null)
    {
        $this->file = $fs ? $fs : new Filesystem();
        $this->zip = $zip ? $zip : new ZipArchive();
    }

    public function make($pathToFile)
    {
        $new = $this->createZipFile($pathToFile);
        $this->openFile($pathToFile, $new);
        return $this;
    }

    public function extractTo($path, array $files = array(), $method = Zipper::BLACKLIST)
    {
        $path = realpath($path);

        if($method == Zipper::WHITELIST)
            $this->extractWithWhiteList($path,$files);
        else
            $this->extractWithBlackList($path, $files);
    }

    public function getFileContent($filePath)
    {
        if($this->zip->locateName($filePath) === false)
            throw new Exception(sprintf('The file "%s" cannot be found', $filePath));

        return $this->zip->getFromName($filePath);
    }

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

    public function getStatus()
    {
        return $this->status;
    }

    public function remove($fileToRemove)
    {
        $this->zip->deleteName($fileToRemove);
    }

    public function close()
    {
        @$this->zip->close();
    }

    //---------------------PRIVATE FUNCTIONS-------------

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

    private function openFile($pathToFile, $create = false)
    {
        if($create)
            $this->status = $this->zip->open($pathToFile, ZipArchive::CREATE);
        else
            $this->status = $this->zip->open($pathToFile);
    }

    private function addDir($pathToDir, $rootDirInZip)
    {
        //is a dir, so remotely go through it and call the add method
        foreach($this->file->allFiles($pathToDir) as $file)
        {
            $this->addFile($pathToDir.'/'.$file->getRelativePathname(),$rootDirInZip);
        }
    }

    private function addFile($pathToAdd, $rootDirInZip)
    {
        empty($rootDirInZip) ? $path = false : $path = true;

        if(!$path)
            $this->zip->addFile($pathToAdd);
        else
            $this->zip->addFile($pathToAdd,$rootDirInZip.'/'.$pathToAdd);
    }

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

    function __destruct()
    {
       @$this->zip->close();
    }
}