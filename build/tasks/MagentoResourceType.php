<?php
/**
 * File MagentoResourceType.php
 *
 * @version GIT $Id$
 */
require_once "phing/types/DataType.php";
/**
 * This Type represents a DB Connection.
 */
class MagentoResourceType extends DataType {

    protected $type;
    /**
     * @var FileSet[]
     */
    protected $files;

    public function addFileSet(FileSet $files)
    {
        $this->files[] = $files;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getFiles()
    {
        $files = array();
        foreach ($this->files as $file) {
            $directoryScanner = $file->getDirectoryScanner($this->getProject());
            $directoryScanner->scan();
            /** @var PhingFile $path */
            $path = $file->getDir($this->getProject());
            if(!isset($files[$path->getAbsolutePath()])) {
                $files[$path->getAbsolutePath()] = array();
            }
            $files[$path->getAbsolutePath()] = array_unique(array_merge($files[$path->getAbsolutePath()], $directoryScanner->getIncludedFiles()));
        }


        return $files;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
