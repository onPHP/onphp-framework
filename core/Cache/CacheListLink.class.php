<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 01.01.14
 * Time: 16:25
 */

class CacheListLink {

    private $keys;

    /**
     * @return self
     */
    public static function create() {
        return new self;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($id, $key) {
        $this->keys[$id] = $key;
        return $this;
    }

    /**
     * @param mixed $keys
     * @return $this
     */
    public function setKeys($keys) {
        $this->keys = $keys;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeys() {
        return $this->keys;
    }
} 