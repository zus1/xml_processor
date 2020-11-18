<?php

namespace XmlProcessor\Interfaces\FileSystemInterface;

interface FileSystemInterface {
    public function getLoadedFiles(string $type);

    public function save(string $type, string $content, string $extension, bool $new=true);

    public function saveSingle(string $fullName, string $content, ?bool $new=true);

    public function load(string $type, int $max=1);

    public function move(array $sourceFiles, string $destinationType);

    public function remove(array $files);
}