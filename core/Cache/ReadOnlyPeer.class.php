<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexander A. Klestov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Cache with read-only access.
 *
 * @ingroup Cache
 **/
class ReadOnlyPeer extends CachePeer
{
    /**
     * @var CachePeer
     */
    private $innerPeer = null;

    /**
     * ReadOnlyPeer constructor.
     * @param CachePeer $peer
     */
    public function __construct(CachePeer $peer)
    {
        $this->innerPeer = $peer;
    }


    /**
     * @return bool
     */
    public function isAlive() : bool
    {
        return $this->innerPeer->isAlive();
    }

    /**
     * @param $className
     * @return CachePeer
     */
    public function mark($className)
    {
        return $this->innerPeer->mark($className);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->innerPeer->get($key);
    }

    /**
     * @param $indexes
     * @return null
     */
    public function getList($indexes)
    {
        return $this->innerPeer->getList($indexes);
    }

    /**
     * @throws UnsupportedMethodException
     */
    public function clean()
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $key
     * @param $value
     * @throws UnsupportedMethodException
     */
    public function increment($key, $value)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $key
     * @param $value
     * @throws UnsupportedMethodException
     */
    public function decrement($key, $value)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $index
     * @param null $time
     * @throws UnsupportedMethodException
     */
    public function delete($index, $time = null)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $key
     * @param $data
     * @throws UnsupportedMethodException
     */
    public function append($key, $data)
    {
        throw new UnsupportedMethodException();
    }

    /**
     * @param $method
     * @param $index
     * @param $value
     * @param int $expires
     * @throws UnsupportedMethodException
     */
    protected function store(
        $method,
        $index,
        $value,
        $expires = Cache::EXPIRES_MINIMUM
    ) {
        throw new UnsupportedMethodException();
    }
}

