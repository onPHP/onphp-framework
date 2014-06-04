<?php
/**
 * Simple config class for Storage engines
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2014.05.18
 */

class StorageConfig extends Singleton{

    protected $configs = array();

    protected $default = null;

    /** @return StorageConfig */
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    public function addConfig(StorageEngineType $type, $link, $config) {
        if(!$config) {
            if(isset($this->configs[$type->getId()][$link])) {
                unset($this->configs[$type->getId()][$link]);
            }
        } else {
            $this->configs[$type->getId()][$link] = $config;
        }
    }

    public function getConfig(StorageEngineType $type, $link) {
        if(!isset($this->configs[$type->getId()][$link])) {
            return array();
        }
        return $this->configs[$type->getId()][$link];
    }

    public function setDefaultEngine(StorageEngineType $engine) {
        $this->default = $engine;
        return $this;
    }

    public function getDefaultEngine() {
        return $this->default;
    }

} 