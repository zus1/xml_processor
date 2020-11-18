<?php
declare(strict_types = 1);

namespace XmlProcessor\Services\Config;

use Exception;

class Config
{
    private static Config $loadedInstance;
    private array $loadedConfiguration = array();

    private function __construct() {
        $this->init();
    }

    /**
     * @throws Exception
     */
    private function init() : void {
        $initFile = dirname(dirname(__FILE__)) . "/init.ini";
        if(!file_exists($initFile)) {
            throw new Exception("Initialization file not found");
        }
        $this->loadedConfiguration = parse_ini_file($initFile);
    }

    /**
     * @return Config
     */
    public static function load() : Config {
        if(empty(self::$loadedInstance)) {
            self::$loadedInstance = new self();
        }

        return self::$loadedInstance;
    }

    /**
     * @param string $key
     * @param string|null $default
     * @return mixed|string|null
     */
    public function get(string $key, ?string $default="") {
        if(isset($this->loadedConfiguration[$key]) && !empty($this->loadedConfiguration[$key])) {
            return $this->loadedConfiguration[$key];
        }

        return $default;
    }
}