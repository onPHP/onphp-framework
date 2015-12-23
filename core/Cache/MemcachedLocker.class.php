<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Memcached based locking.
 * No synchronization between local pool and memcached daemons!
 *
 * @ingroup Lockers
 **/
class MemcachedLocker extends BaseLocker implements Instantiatable
{
    const VALUE = 0x1;

    /**
     * @var CachePeer
     */
    private $memcachedClient = null;

    /**
     * @return MemcachedLocker
     */
    public static function me() : MemcachedLocker
    {
        return Singleton::getInstance(__CLASS__);
    }

    /**
     * @param CachePeer $memcachedPeer
     * @return MemcachedLocker
     */
    public function setMemcachedClient(CachePeer $memcachedPeer) : MemcachedLocker
    {
        $this->memcachedClient = $memcachedPeer;

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcachedClient->add(
            $key,
            self::VALUE,
            2 * Cache::EXPIRES_MINIMUM
        );
    }

    /**
     * @param $key
     * @return mixed
     */
    public function free($key)
    {
        return $this->memcachedClient->delete($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function drop($key)
    {
        return $this->free($key);
    }
}
