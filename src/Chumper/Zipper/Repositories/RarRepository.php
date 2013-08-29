<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nils
 * Date: 28.08.13
 * Time: 20:37
 * To change this template use File | Settings | File Templates.
 */

namespace Chumper\Zipper\Repositories;


class RarRepository implements RepositoryInterface
{

    /**
     * Construct with a given path
     *
     * @param $filePath
     */
    function __construct($filePath)
    {
        // TODO: Implement __construct() method.
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
        // TODO: Implement addFile() method.
    }

    /**
     * Remove a file permanently from the Archive
     *
     * @param $pathInArchive
     * @return void
     */
    public function removeFile($pathInArchive)
    {
        // TODO: Implement removeFile() method.
    }

    /**
     * Get the content of a file
     *
     * @param $pathInArchive
     * @return string
     */
    public function getFileContent($pathInArchive)
    {
        // TODO: Implement getFileContent() method.
    }

    /**
     * Get the stream of a file
     *
     * @param $pathInArchive
     * @return mixed
     */
    public function getFileStream($pathInArchive)
    {
        // TODO: Implement getFileStream() method.
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
        // TODO: Implement each() method.
    }

    /**
     * Checks whether the file is in the archive
     *
     * @param $fileInArchive
     * @return boolean
     */
    public function fileExists($fileInArchive)
    {
        // TODO: Implement fileExists() method.
    }

    /**
     * Returns the status of the archive as a string
     *
     * @return string
     */
    public function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    /**
     * Closes the archive and saves it
     * @return void
     */
    public function close()
    {
        // TODO: Implement close() method.
    }
}