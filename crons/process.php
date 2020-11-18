<?php

use XmlProcessor\FileSystem\FileSystem;
use XmlProcessor\XmlProcessor\XmlProcessor;

include_once(dirname(dirname(__FILE__)) . "/autoload.php");

try {
    $xmlProcessor = new XmlProcessor(new FileSystem());
    $xmlProcessor->process();
} catch(Exception $e) {
    echo __FILE__  . ": " . $e->getMessage();
}

echo "Process: Ok";