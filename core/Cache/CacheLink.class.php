<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 01.01.14
 * Time: 15:15
 */

class CacheLink {

    private $key;

    /**
     * @return self
     */
    public static function create() {
        return new self;
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }
} 