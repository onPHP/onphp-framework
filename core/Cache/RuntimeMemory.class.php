<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Default process RAM cache.
 *
 * @ingroup Cache
 **/
class RuntimeMemory extends CachePeer
{
    private $cache = array();

    /**
     * @deprecated
     *
     * @return RuntimeMemory
     **/
    public static function create() : RuntimeMemory
    {
        return new self;
    }

    /**
     * @return bool
     */
    public function isAlive() : bool
    {
        return true;
    }

    /**
     * @param $key
     * @param $value
     * @return null
     */
    public function increment($key, $value)
    {
        if (isset($this->cache[$key]))
            return $this->cache[$key] += $value;

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return null
     */
    public function decrement($key, $value)
    {
        if (isset($this->cache[$key]))
            return $this->cache[$key] -= $value;

        return null;
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        if (isset($this->cache[$key]))
            return $this->cache[$key];

        return null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
            return true;
        }

        return false;
    }

    /**
     * @return RuntimeMemory
     **/
    public function clean()
    {
        $this->cache = array();

        return parent::clean();
    }

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function append($key, $data)
    {
        if (isset($this->cache[$key])) {
            $this->cache[$key] .= $data;
            return true;
        }

        return false;
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return bool
     */
    protected function store($action, $key, $value, $expires = 0)
    {
        if ($action == 'add' && isset($this->cache[$key]))
            return true;
        elseif ($action == 'replace' && !isset($this->cache[$key]))
            return false;

        if (is_object($value))
            $this->cache[$key] = clone $value;
        else
            $this->cache[$key] = $value;

        return true;
    }
}
