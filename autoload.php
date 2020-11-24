<?php

spl_autoload_register(function() {
    $documentRoot = dirname(__FILE__);
    $includePathMap = array(
        $documentRoot . "/ValueObjects",
        $documentRoot . "/Interfaces",
        $documentRoot . "/Services/Context",
        $documentRoot . "/Services",
    );
    foreach($includePathMap as $path) {
        if(is_dir($path)) {
            $files = scandir($path);
            foreach($files as $file) {
                if($file != "." && $file !== ".." && !is_dir($file)) {
                    $fullPath = $path . "/" . $file;
                    if(file_exists($fullPath) && !is_dir($fullPath)) {
                        include_once($fullPath);
                    }
                }
            }
        }
    }
});
