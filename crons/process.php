<?php

use XmlProcessor\FileSystem\FileSystem;
use XmlProcessor\ValueObjects\FetchUrls;
use XmlProcessor\XmlProcessor\XmlProcessor;

include_once(dirname(dirname(__FILE__)) . "/autoload.php");

try {
    $xmlProcessor = new XmlProcessor(new FileSystem(), new FetchUrls());
    $xmlProcessor->process();
} catch(Exception $e) {
    echo __FILE__  . ": " . $e->getMessage() . PHP_EOL;
    die();
}

echo "Process: Ok" . PHP_EOL;