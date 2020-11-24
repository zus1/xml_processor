<?php
declare(strict_types = 1);

namespace XmlProcessor\XmlProcessor;

use Exception;
use XmlProcessor\FileSystem\FileSystem;
use XmlProcessor\Interfaces\FileSystemInterface\FileSystemInterface;
use XmlProcessor\Interfaces\ProcessorInterface\ProcessorInterface;
use XmlProcessor\Services\Config\Config;
use XmlProcessor\ValueObjects\FetchUrls;
use XmlProcessor\ValueObjects\XmlToArray\XmlToArray;

class XmlProcessor implements ProcessorInterface {
    private FileSystemInterface $fileSystem;
    private FetchUrls $urls;
    private array $fetchUrls = array();
    private int $maxProcessPerCycle;
    private array $allowedFetchNumber = array();

    public function __construct(FileSystemInterface $filesystem, FetchUrls $urls) {
        $this->fileSystem = $filesystem;
        $this->urls = $urls;
        $this->maxProcessPerCycle = (int)Config::load()->get("MAX_PROCESS_PER_CYCLE", "3");
        $this->allowedFetchNumber = explode(",", Config::load()->get("ALLOWED_FETCH_NUMBER", "20,200,2000"));
        $this->fetchUrls = explode(",", Config::load()->get("FETCH_URLS", "http://markerdev.info/backend/data2.xml"));
    }

    /**
     * Fetches xml and saves it to file
     */
    public function fetch() : void {
        $this->removeOldFetchedFiles();
        $urlsData = $this->urls->getUrlData();
        array_walk($urlsData, function (array $urlData) {
            $contextClass = $urlData["context_class"];
            $context = (new $contextClass())->buildFetchContext();
            $xml = file_get_contents($urlData["url"], false, $context);
            $this->validate($xml);
            $this->fileSystem->save(FileSystem::RAW, $xml, FileSystem::EXTENSION_XML);
        });
    }

    /**
     * Removed old xml file, when new one is fetched
     */
    private function removeOldFetchedFiles() : void {
        $this->fileSystem->load(FileSystem::RAW);
        $rawLoadedFiles = $this->fileSystem->getLoadedFiles(FileSystem::RAW);
        if(count($rawLoadedFiles) > 0) {
            $this->fileSystem->remove($rawLoadedFiles);
        }
    }

    /**
     *
     * Separates input raw xml to multiple xml's, each one containing single product
     *
     * @throws Exception
     */
    public function split() : void {
        $contents = $this->fileSystem->load(FileSystem::RAW);
        if(empty($contents)) {
            throw new Exception(__METHOD__ ."_No data");
        }
        array_walk($contents, function(string $content) {
           $xmlObj = simplexml_load_string($content);
           foreach($xmlObj->children() as $child) {
               usleep(1000);
               $xml = $child->asXML();
               $this->fileSystem->save(FileSystem::PROCESS, $xml, FileSystem::EXTENSION_XML);
           }
        });
    }

    /**
     *
     * Process single product xml's and saves results to array
     *
     * @throws Exception
     */
    public function process() : void {
        $inputContents = $this->getContentsForProcessing();
        $outputContents = $this->processContents($inputContents);
        $this->storeProcessingResults($outputContents);
        $this->moveProcessedFiles();
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getContentsForProcessing() : array {
        $contents = $this->fileSystem->load(FileSystem::PROCESS, $this->maxProcessPerCycle);
        if(empty($contents)) {
            throw new Exception(__METHOD__ ."_No data");
        }

        return $contents;
    }

    /**
     * @param array $inputContents
     * @return array
     */
    private function processContents(array $inputContents) : array {
        $outputContents = array();
        array_walk($inputContents, function(string $content) use(&$outputContents) {
            $xml2Array = new XmlToArray();
            $outputContents[] = $xml2Array->convert($content);
        });

        return $outputContents;
    }

    /**
     * @param array $outputContents
     * @throws Exception
     */
    private function storeProcessingResults(array $outputContents) : void {
        $results = $this->fileSystem->load(FileSystem::RESULTS);
        if(count($results) > 1) {
            throw new Exception("There should be only one processed array, " . count($results) . " detected");
        }

        if(empty($results)) {
            $this->fileSystem->save(FileSystem::RESULTS, serialize($outputContents), FileSystem::EXTENSION_TXT);
        } else {
            $loadedFiles = $this->fileSystem->getLoadedFiles(FileSystem::RESULTS);
            if(count($loadedFiles) > 1) {
                throw new Exception("There should be only one processed file loaded, " . count($loadedFiles) . " detected");
            }
            $fullFileName = $loadedFiles[0];
            $alreadyProcessed = unserialize($results[0]);
            $this->fileSystem->saveSingle($fullFileName, serialize(array_merge($outputContents, $alreadyProcessed)), false);
        }
    }

    /**
     * Moves files from Process to Processed
     */
    private function moveProcessedFiles() : void {
        $loadedProcessFiles = $this->fileSystem->getLoadedFiles(FileSystem::PROCESS);
        $this->fileSystem->move($loadedProcessFiles, FileSystem::PROCESSED);
    }

    /**
     * @param string $content
     * @throws Exception
     */
    private function validate(string $content) : void {
        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($content);
        if(!$xmlObj) {
            libxml_clear_errors();
            throw new Exception("Invalid Xml");
        }
        $this->checkXmlCount($xmlObj->count());
    }

    private function checkXmlCount(int $count) : void {
        if(!in_array($count, $this->allowedFetchNumber)) {
            throw new Exception("Invalid xml count found.");
        }
    }
}