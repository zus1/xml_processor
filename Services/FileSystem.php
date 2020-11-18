<?php
declare(strict_types = 1);

namespace XmlProcessor\FileSystem;

use Exception;
use XmlProcessor\Interfaces\FileSystemInterface\FileSystemInterface;
use XmlProcessor\ValueObjects\DateWithMicrotime\DateWithMicrotime;

class FileSystem implements FileSystemInterface {

    const RAW = "raw";
    const PROCESS = "process";
    const PROCESSED = "processed";
    const RESULTS = "results";

    const EXTENSION_XML = "xml";
    const EXTENSION_TXT = "txt";
    private string $masterDir;
    private array $loadedFiles = array();

    public function __construct() {
        $this->masterDir = dirname(dirname(__FILE__)) . "/Resources";
    }

    /**
     * @param string $type
     * @return array|mixed
     */
    public function getLoadedFiles(string $type) {
        if(isset($this->loadedFiles[$type])) {
            return $this->loadedFiles[$type];
        }

        return array();
    }

    /**
     * @return array
     */
    private function getAllowedExtensions() {
        return array(self::EXTENSION_XML, self::EXTENSION_TXT);
    }

    /**
     * @return array
     */
    private function getAllowedTypes() {
        return array(self::RAW, self::PROCESS, self::PROCESSED, self::RESULTS);
    }

    /**
     *
     * Saves a file of type $type to local storage
     *
     * @param string $type
     * @param string $content
     * @param string $extension
     * @param bool $new
     * @throws Exception
     */
    public function save(string $type, string $content, string $extension, bool $new=true) : void {
        $this->checkIfValidType($type);
        $this->checkIfValidExtension($extension);
        $dirname = $this->makeSureDirectoryExists($type);
        $filename = $this->makeFileName($type, $extension);
        $fullName = sprintf("%s/%s", $dirname, $filename);

        $this->saveSingle($fullName, $content, $new);
    }

    /**
     *
     * Saves single file by filename, to local storage
     *
     * @param string $fullName
     * @param string $content
     * @param bool|null $new
     * @throws Exception
     */
    public function saveSingle(string $fullName, string $content, ?bool $new=true) : void {
        if($new === true && file_exists($fullName)) {
            throw new Exception("File " . $fullName . " already exists");
        }

        file_put_contents($fullName, $content);

        if(!file_exists($fullName)) {
            throw new Exception("Could not save file");
        }
    }

    /**
     *
     * Loads a total of $total files of type $type, from local storage
     *
     * @param string $type
     * @param int $total
     * @return array
     * @throws Exception
     */
    public function load(string $type, int $total=0) : array {
        $this->checkIfValidType($type);
        $dirName = sprintf("%s/%s", $this->masterDir, ucfirst($type));
        $contents = array();
        if(is_dir($dirName)) {
            $total = $this->getTotalLoad($total, $dirName);
            $dh = opendir($dirName);
            while(($file = readdir($dh)) !== false) {
                $fullName = "";
                $content = $this->loadContent($file, $dirName, $fullName);
                if($content !== "") {
                    $contents[] = $content;
                    $this->loadedFiles[$type][] = $fullName;
                    $total--;
                    if($total <= 0) {
                        break;
                    }
                }
            }
        }

        return $contents;
    }

    /**
     *
     * Applies default to $total parameter
     *
     * @param int $total
     * @param string $dirName
     * @return int
     */
    private function getTotalLoad(int $total, string $dirName) : int {
        if($total === 0) {
            return $this->getDirectoryTotal($dirName);
        }

        return $total;
    }

    /**
     * @param string $dirname
     * @return int
     */
    private function getDirectoryTotal(string $dirname) : int {
        return count(scandir($dirname)) - 2 ;
    }

    /**
     *
     * Laads content for single file
     *
     * @param string $file
     * @param string $dirName
     * @param $fullName
     * @return string
     */
    private function loadContent(string $file, string $dirName, &$fullName) : string {
        if($file !== "." && $file !== "..") {
            $fullName = sprintf("%s/%s", $dirName, $file);
            if(is_readable($fullName)) {
                return file_get_contents($fullName);
            }
        }

        return "";
    }

    /**
     *
     * Moves files to directory of $type
     *
     * @param array $sourceFiles
     * @param string $destinationType
     */
    public function move(array $sourceFiles, string $destinationType) : void {
        $this->makeSureDirectoryExists($destinationType);
        $destinationDir = sprintf("%s/%s", $this->masterDir, ucfirst($destinationType));
        array_walk($sourceFiles, function (string $fullSourceName) use($destinationDir, $destinationType) {
           if(file_exists($fullSourceName)) {
               $ext = $this->getFileExtension($fullSourceName);
               $destinationFileName = $this->makeFileName($destinationType, $ext);
               $fullDestinationName = sprintf("%s/%s", $destinationDir, $destinationFileName);
               rename($fullSourceName, $fullDestinationName);
           }
        });
    }

    /**
     * @param array $files
     */
    public function remove(array $files) : void {
        array_walk($files, function (string $fullName) {
            if(file_exists($fullName)) {
                unlink($fullName);
            }
        });
    }

    /**
     * @param string $type
     * @return string
     */
    private function makeSureDirectoryExists(string $type) : string {
        $dirName = sprintf("%s/%s", $this->masterDir, ucfirst($type));
        if(!is_dir($dirName)) {
            mkdir($dirName, 0775);
        }

        return $dirName;
    }

    /**
     *
     * Appends fate and microtime to filename
     *
     * @param string $type
     * @param string $extension
     * @return string
     */
    private function makeFileName(string $type, string $extension) : string {
        $dwm = new DateWithMicrotime();
        $suffix = $dwm->get();
        return sprintf("%s_%s.%s", $type, $suffix, $extension);
    }

    /**
     * @param string $type
     * @throws Exception
     */
    private function checkIfValidType(string $type) : void {
        if(!in_array($type, $this->getAllowedTypes())) {
            throw new Exception("Invalid file type");
        }
    }

    /**
     * @param string $extension
     * @throws Exception
     */
    private function checkIfValidExtension(string $extension) : void {
        if(!in_array($extension, $this->getAllowedExtensions())) {
            throw new Exception("File extension not allowed");
        }
    }

    /**
     * @param string $fullName
     * @return string
     */
    private function getFileExtension(string $fullName) : string {
        $pathInfo = pathinfo($fullName);
        return $pathInfo["extension"];
    }
}