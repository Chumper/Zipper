<?php namespace Chumper\Zipper\Repositories;

use ZipArchive;

class ZipRepository implements RepositoryInterface
{
    private $archive;

    /**
     * Construct with a given path
     *
     * @param $filePath
     * @param null $archive
     * @return \Chumper\Zipper\Repositories\ZipRepository
     */
    function __construct($filePath, $archive = null)
    {
        $this->archive = $archive ? $archive : new ZipArchive;
    }

    /**
     * Add a file to the opened Archive
     *
     * @param $pathToFile
     * @param $pathInArchive
     * @return void
     */
    public function addFile($pathToFile, $pathInArchive)
    {
        $this->archive->addFile($pathToFile, $pathInArchive);
    }

    /**
     * Remove a file permanently from the Archive
     *
     * @param $pathInArchive
     * @return void
     */
    public function removeFile($pathInArchive)
    {
        $this->archive->deleteName($pathInArchive);
    }

    /**
     * Get the content of a file
     *
     * @param $pathInArchive
     * @return string
     */
    public function getFileContent($pathInArchive)
    {
        return $this->archive->getFromName($pathInArchive);
    }

    /**
     * Get the stream of a file
     *
     * @param $pathInArchive
     * @return mixed
     */
    public function getFileStream($pathInArchive)
    {
        return $this->archive->getStream($pathInArchive);
    }

    /**
     * Will loop over every item in the archive and will execute the callback on them
     * Will provide the filename for every item
     *
     * @param $callback
     * @return void
     */
    public function each($callback)
    {
        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            //check if folder
            $stats = $this->archive->statIndex($i);
            if ($stats['size'] == 0 && $stats['crc'] == 0)
                continue;

            call_user_func_array($callback, array(
                'file' => $this->archive->getNameIndex($i),
            ));
        }
    }

    /**
     * Checks whether the file is in the archive
     *
     * @param $fileInArchive
     * @return boolean
     */
    public function fileExists($fileInArchive)
    {
        return $this->archive->locateName($fileInArchive) !== false;
    }

    /**
     * Returns the status of the archive as a string
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->archive->getStatusString();
    }

}