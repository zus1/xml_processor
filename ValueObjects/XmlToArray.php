<?php
declare(strict_types = 1);

namespace XmlProcessor\ValueObjects\XmlToArray;

use XmlProcessor\Interfaces\FileSystemInterface\ToArray;

class XmlToArray implements ToArray
{
    /**
     * @param string $payload
     * @return array
     */
    public function convert(string $payload) : array {
        $array = array("product" => array(), "description" => array());
        $xmlObj = simplexml_load_string($payload);
        $attributes = $xmlObj->attributes();
        foreach($attributes as $name => $value) {
            $value = sprintf("%s", $value);
            $array["product"][$name] = $value;
        }
        foreach($xmlObj->children() as $child) {
            $name = $child->attributes()[0]->getName();
            if($name !== "lang") {
                continue;
            }
            $lang = sprintf("%s", $child->attributes()[0]);
            $title = sprintf("%s", $child->title);
            $description = sprintf("%s", $child->description);
            $array["description"][$lang] = array(
                "title" => $title,
                "description" => $description,
            );
        }
        return $array;
    }
}