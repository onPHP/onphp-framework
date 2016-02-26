<?php

/****************************************************************************
 *   Copyright (C) 2012 by Artem Naumenko                                   *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
class SequentialCache extends CachePeer
{
    /**
     * List of all peers, including master
     * @var array of CachePeer
     */
    private $list = [];

    /**
     * List of slaves only
     * @var array of CachePeer
     */
    private $slaves = [];

    /**
     * @var CachePeer
     */
    private $master = null;

    /**
     * @param CachePeer $master
     * @param array $slaves or CachePeer
     */
    public function __construct(CachePeer $master, array $slaves = [])
    {
        $this->setMaster($master);

        foreach ($slaves as $cache) {
            $this->addPeer($cache);
        }
    }

    /**
     * @param CachePeer $master
     * @return \SequentialCache
     */
    public function setMaster(CachePeer $master)
    {
        $this->master = $master;
        $this->list = $this->slaves;
        array_unshift($this->list, $this->master);

        return $this;
    }

    /**
     * @param CachePeer $peer
     * @return $this
     */
    public function addPeer(CachePeer $peer)
    {
        $this->list[] = $peer;
        $this->slaves[] = $peer;

        return $this;
    }


    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        foreach ($this->list as $val) {
            /* @var $val CachePeer */
            $result = $val->get($key);

            if (
                !empty($result)
                || $val->isAlive()
            ) {
                return $result;
            }
        }

        throw new RuntimeException('All peers are dead');
    }

    /**
     * @param $key
     * @param $data
     * @return bool
     */
    public function append($key, $data)
    {
        return $this->foreachItem(__METHOD__, func_get_args());
    }

    /**
     * @param $method
     * @param array $args
     * @return bool
     */
    private function foreachItem($method, array $args)
    {
        $result = true;

        foreach ($this->list as $peer) {
            /* @var $peer CachePeer */
            $result = call_user_func_array([$peer, $method], $args) && $result;
        }

        return $result;
    }

    /**
     * @param $key
     * @param $value
     * @throws UnsupportedMethodException
     */
    public function decrement($key, $value)
    {
        throw new UnsupportedMethodException('decrement is not supported');
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->foreachItem(__METHOD__, func_get_args());
    }

    /**
     * @param $key
     * @param $value
     * @throws UnsupportedMethodException
     */
    public function increment($key, $value)
    {
        throw new UnsupportedMethodException('increment is not supported');
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return bool
     */
    protected function store($action, $key, $value, $expires = Cache::EXPIRES_MEDIUM)
    {
        return $this->foreachItem(__METHOD__, func_get_args());
    }
}
