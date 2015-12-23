<?php
/***************************************************************************
 *   Copyright (C) 2006-2012 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Connector for PECL's Memcache extension by Antony Dovgal.
 *
 * @see http://tony2001.phpclub.net/
 * @see http://pecl.php.net/package/memcache
 *
 * @ingroup Cache
 **/
class PeclMemcached extends CachePeer
{
    const
        DEFAULT_PORT = 11211,
        DEFAULT_HOST = '127.0.0.1',
        DEFAULT_TIMEOUT = 1;

    protected $host = null;
    protected $port = null;
    private $requestTimeout = null;
    private $connectTimeout = null;
    private $triedConnect = false;

    /** @var Memcache */
    private $instance = null;

    /**
     * @param string $host
     * @param int $port
     * @param int $connectTimeout
     * @return PeclMemcached
     */
    public static function create(
        $host = self::DEFAULT_HOST,
        $port = self::DEFAULT_PORT,
        $connectTimeout = self::DEFAULT_TIMEOUT
    )
    {
        return new self($host, $port, $connectTimeout);
    }

    /**
     * PeclMemcached constructor.
     * @param string $host
     * @param int $port
     * @param int $connectTimeout
     */
    public function __construct(
        $host = self::DEFAULT_HOST,
        $port = self::DEFAULT_PORT,
        $connectTimeout = self::DEFAULT_TIMEOUT
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @see __destruct
     */
    public function __destruct()
    {
        if ($this->alive) {
            try {
                $this->instance->close();
            } catch (BaseException $e) {
                // shhhh.
            }
        }
    }

    /**
     * @return bool
     */
    public function isAlive() : bool
    {
        $this->ensureTriedToConnect();

        return parent::isAlive();
    }

    /**
     * @return PeclMemcached
     **/
    public function clean()
    {
        $this->ensureTriedToConnect();

        try {
            $this->instance->flush();
        } catch (BaseException $e) {
            $this->alive = false;
        }

        return parent::clean();
    }

    /**
     * @param $key
     * @param $value
     * @return bool|null
     */
    public function increment($key, $value)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->instance->increment($key, $value);
        } catch (BaseException $e) {
            return null;
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
            return $this->instance->decrement($key, $value);
        } catch (BaseException $e) {
            return null;
        }
    }

    /**
     * @param $indexes
     * @return array|null|string
     */
    public function getList($indexes)
    {
        $this->ensureTriedToConnect();

        return
            ($return = $this->get($indexes))
                ? $return
                : array();
    }

    /**
     * @param $index
     * @return array|null|string
     * @throws WrongArgumentException
     */
    public function get($index)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->instance->get($index);
        } catch (BaseException $e) {
            if (strpos($e->getMessage(), 'Invalid key') !== false)
                return null;

            $this->alive = false;

            return null;
        }

        Assert::isUnreachable();
    }

    /**
     * @param $index
     * @return bool
     * @throws WrongArgumentException
     */
    public function delete($index)
    {
        $this->ensureTriedToConnect();

        try {
            // second parameter required, wrt new memcached protocol:
            // delete key 0 (see process_delete_command in the memcached.c)
            // Warning: it is workaround!
            return $this->instance->delete($index, 0);
        } catch (BaseException $e) {
            return $this->alive = false;
        }

        Assert::isUnreachable();
    }

    /**
     * @param $key
     * @param $data
     * @return bool|void
     * @throws WrongArgumentException
     */
    public function append($key, $data)
    {
        $this->ensureTriedToConnect();

        try {
            return $this->instance->append($key, $data);
        } catch (BaseException $e) {
            return $this->alive = false;
        }

        Assert::isUnreachable();
    }

    /**
     * @param float $requestTimeout time in seconds
     * @return PeclMemcached
     */
    public function setTimeout($requestTimeout)
    {
        $this->ensureTriedToConnect();
        $this->requestTimeout = $requestTimeout;
        $this->instance->setServerParams($this->host, $this->port, $requestTimeout);

        return $this;
    }

    /**
     * @return float
     */
    public function getTimeout()
    {
        return $this->requestTimeout;
    }

    /**
     * @return PeclMemcached
     */
    protected function ensureTriedToConnect() : PeclMemcached
    {
        if ($this->triedConnect)
            return $this;

        $this->triedConnect = true;

        $this->connect();

        return $this;
    }

    /**
     * @param $action
     * @param $key
     * @param $value
     * @param int $expires
     * @return bool
     * @throws WrongArgumentException
     */
    protected function store(
        $action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
    )
    {
        $this->ensureTriedToConnect();

        try {
            return
                $this->instance->$action(
                    $key,
                    $value,
                    $this->compress
                        ? MEMCACHE_COMPRESSED
                        : false,
                    $expires
                );
        } catch (BaseException $e) {
            return $this->alive = false;
        }

        Assert::isUnreachable();
    }

    /**
     * @see connect
     */
    protected function connect()
    {
        $this->instance = new Memcache();

        try {

            try {
                $this->instance->pconnect($this->host, $this->port, $this->connectTimeout);
            } catch (BaseException $e) {
                $this->instance->connect($this->host, $this->port, $this->connectTimeout);
            }

            $this->alive = true;

        } catch (BaseException $e) {
            // bad luck.
        }
    }
}

