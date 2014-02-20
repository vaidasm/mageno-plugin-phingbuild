<?php
/**
 * File BuildMagentoXml.php
 *
 * @version GIT $Id$
 */
require_once "phing/Task.php";
/**
 * Class BuildMagentoXml
 */
class BuildMagentoXmlTask extends Task
{
    protected $property = "magento.contentsxml";
    /**
     * @var MagentoResourceType[]
     */
    protected $resource = array();

    /**
     * @param $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @param MagentoResourceType $resource
     */
    public function addResource(MagentoResourceType $resource)
    {
        $this->resource[] = $resource;
    }

    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        // nothing to do here
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        $xml = new SimpleXMLElement("<contents/>");
        foreach ($this->getTree() as $target => $targetFiles) {
            $targetElement = $xml->addChild('target');
            $targetElement->addAttribute('name', $target);
            $this->addFilesToXml($targetElement, $targetFiles);
        }

        $xml = $xml->asXML();
        $this->getProject()->setNewProperty($this->property, substr($xml, strpos($xml, '?>') + 2));
    }

    private function addFileToArray(&$target, $file, $baseDir)
    {
        list($dir, $fileLeft) = explode('/', $file, 2);

        if (strpos($fileLeft, '/') !== false) {
            if (!isset($target[$dir])) {
                $target[$dir] = array();
            }
            $this->addFileToArray($target[$dir], $fileLeft, $baseDir.'/'.$dir);
        } else {
            $target[$dir][] = new PhingFile($baseDir.'/'.$dir.'/'.$fileLeft);
        }

        return $target;
    }

    /**
     * @return array
     */
    public function getTree()
    {
        $target = array();
        foreach ($this->resource as $resource) {
            $type = $resource->getType();
            foreach ($resource->getFiles() as $baseDir=>$dirFiles) {
                foreach ($dirFiles as $file) {
                    $this->addFileToArray($target[$type], $file, $baseDir);
                }
            }
        }
        return $target;
    }

    /**
     * @param SimpleXmlElement $target
     * @param array $targetFiles
     */
    private function addFilesToXml($target, $targetFiles)
    {
        foreach ($targetFiles as $file => $files) {

            if(is_int($file)) {
                $dir = $target;
            } else {
                $dir = $target->addChild('dir');
                $dir->addAttribute('name', $file);

            }


            if($files instanceof PhingFile) {
                $fileElement = $dir->addChild('file');
                $fileElement->addAttribute('name', $files->getName());
                $fileElement->addAttribute('hash', md5_file($files->getAbsolutePath()));
            } else {
                $this->addFilesToXml($dir, $files);
            }
        }
    }
}
