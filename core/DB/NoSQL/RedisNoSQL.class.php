<?php

/***************************************************************************
 *   Copyright (C) 2012 by Artem Naumenko                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class RedisNoSQL extends CachePeer implements ListGenerator
{
    const
        DEFAULT_HOST = 'localhost',
        DEFAULT_PORT = '6379',
        DEFAULT_TIMEOUT = 1.0;

    private $host = null;
    private $port = null;
    private $timeout = null;
    private $triedConnect = false;

    /** @var Redis */
    private $redis = null;

    /**
     * RedisNoSQL constructor.
     * @param string $host
     * @param string $port
     * @param float $timeout
     */
    public function __construct(
        $host = self::DEFAULT_HOST,
        $port = self::DEFAULT_PORT,
        $timeout = self::DEFAULT_TIMEOUT
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    /**
     * @see __destruct
     */
    public function __destruct()
    {
        if ($this->alive) {
            try {
                $this->redis->close();        //if pconnect - it will be ignored
            } catch (RedisException $e) {
                // shhhh.
            }
        }
    }

    /**
     * @return RedisNoSQL
     */
    public function clean()
    {
        $this->ensureTriedToConnect();

        try {
            $this->redis->flushDB();
        } catch (RedisException $e) {
            $this->alive = false;
        }

        return parent::clean();
    }

    /**
     * @return RedisNoSQL
     */
    protected function ensureTriedToConnect()
    {
        if ($this->triedConnect) {
            return $this;
        }

        $this->triedConnect = true;

        /** @var Redis redis */
        $this->redis = new Redis();

        try {
            $this->redis->pconnect($this->host, $this->port, $this->timeout);
            $this->isAlive();
        } catch (RedisException $e) {
            $this->alive = false;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        $this->ensureTriedToConnect();

        try {
            $this->alive = $this->redis->ping() == '+PONG';
        } catch (RedisException $e) {
            $this->alive = false;
        }

        return parent::isAlive();
    }

    /**
     * @param $key
     * @param $data
     * @return bool|int
     */
    public function append($key, $data)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->redis->append($key, $data);
        } catch (RedisException $e) {
            return $this->alive = false;
        }
    }

    /**
     * @param $key
     * @param $value
     * @return int|null
     */
    public function decrement($key, $value)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->redis->decrBy($key, $value);
        } catch (RedisException $e) {
            return null;
        }
    }

    /**
     * @param $key
     * @return bool|void
     */
    public function delete($key)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->redis->delete($key);
        } catch (RedisException $e) {
            return $this->alive = false;
        }
    }

    /**
     * @param $key
     * @return bool|null|string
     */
    public function get($key)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->redis->get($key);
        } catch (RedisException $e) {
            $this->alive = false;

            return null;
        }
    }

    /**
     * @param $key
     * @param $value
     * @return int|null
     */
    public function increment($key, $value)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->redis->incrBy($key, $value);
        } catch (RedisException $e) {
            return null;
        }
    }

    /**
     * @param string $key
     *
     * @return RedisNoSQLList
     */
    public function fetchList($key, $timeout = null)
    {
        $this->ensureTriedToConnect();

        return new RedisNoSQLList($this->redis, $key, $timeout);
    }

    /**
     * @param $key
     * @throws UnimplementedFeatureException
     */
    public function fetchSet($key)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @param $key
     * @throws UnimplementedFeatureException
     */
    public function fetchHash($key)
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return bool
     * @throws UnimplementedFeatureException
     */
    protected function store($action, $key, $value, $expires = Cache::EXPIRES_MEDIUM)
    {
        $this->ensureTriedToConnect();

        switch ($action) {
            case 'set':
            case 'replace':
            case 'add':
                try {
                    $result = $this->redis->set($key, $value);
                    $this->redis->expire($key, $expires);
                    return $result;
                } catch (RedisException $e) {
                    return $this->alive = false;
                }

            default:
                throw new UnimplementedFeatureException();
        }
    }
}

