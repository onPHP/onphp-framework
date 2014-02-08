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

final class RedisNoSQL extends CachePeer implements ListGenerator
{
	const DEFAULT_HOST = 'localhost';
	const DEFAULT_PORT = '6379';
	const DEFAULT_TIMEOUT = 1.0;

	private $redis			= null;
	private $host			= null;
	private $port			= null;
	private $timeout		= null;
	private $triedConnect	= false;

	/**
	 * @param type $host
	 * @param type $port
	 * @param type $timeout
	 * @return RedisNoSQL
	 */
	public static function create(
		$host = self::DEFAULT_HOST,
		$port = self::DEFAULT_PORT,
		$timeout = self::DEFAULT_TIMEOUT,
		$db = 0
	)
	{
		$instance = new self($host, $port, $timeout);
		$instance->select($db);
		return $instance;
	}

	public function __construct(
		$host = self::DEFAULT_HOST,
		$port = self::DEFAULT_PORT,
		$timeout = self::DEFAULT_TIMEOUT
	)
	{
		$this->host		= $host;
		$this->port		= $port;
		$this->timeout	= $timeout;
	}

	public function __destruct()
	{
		if ($this->alive) {
			try {
				$this->redis->close();		//if pconnect - it will be ignored
			} catch (RedisException $e) {
				// shhhh.
			}
		}
	}

	public function clean()
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
			$this->redis->flushDB();
            $profiling
                ->setInfo('clean')
                ->end()
            ;
		} catch (RedisException $e) {
			$this->alive = false;
		}

		return parent::clean();
	}

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

	public function append($key, $data)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
			$response = $this->redis->append($key, $data);
            $profiling
                ->setInfo('append ' . $key)
                ->end()
            ;
			return $response;
		} catch (RedisException $e) {
			return $this->alive = false;
		}
	}

	public function decrement($key, $value)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
            $response = $this->redis->decrBy($key, $value);
            $profiling
                ->setInfo('decrement ' . $key)
                ->end()
            ;
            return $response;
		} catch (RedisException $e) {
			return null;
		}
	}

	public function delete($key)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
            $response = $this->redis->delete($key);
            $profiling
                ->setInfo('delete ' . $key)
                ->end()
            ;
            return $response;
		} catch (RedisException $e) {
			return $this->alive = false;
		}
	}

	public function get($key)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
            $response = $this->redis->get($key);
            $profiling
                ->setInfo('get ' . $key)
                ->end()
            ;
            return $response;
		} catch (RedisException $e) {
			$this->alive = false;

			return null;
		}
	}

	public function keys($mask)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
            $response = $this->redis->keys($mask);
            $profiling
                ->setInfo('keys ' . $mask)
                ->end()
            ;
            return $response;
		} catch (RedisException $e) {
			$this->alive = false;

			return null;
		}
	}

	public function increment($key, $value)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		try {
            $response = $this->redis->incrBy($key, $value);
            $profiling
                ->setInfo('increment ' . $key)
                ->end()
            ;
            return $response;
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
	 * @param string $key
	 *
	 * @return RedisNoSQLSet
	 */
	public function fetchSet($key)
	{
		throw new UnimplementedFeatureException();
	}

	/**
	 * @param string $key
	 *
	 * @return RedisNoSQLHash
	 */
	public function fetchHash($key)
	{
		throw new UnimplementedFeatureException();
	}

	public function info() {
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
        $response = $this->redis->info();
        $profiling
            ->setInfo('info')
            ->end()
        ;
        return $response;
	}

	public function select($db) {
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		if( is_null($db) || !Assert::checkInteger($db) ) {
			throw new WrongArgumentException('DB id should be an integer');
		}
		$result = $this->redis->select($db);
		if( !$result ) {
			throw new WrongStateException('could not change db');
		}
        $profiling
            ->setInfo('select ' . $db)
            ->end()
        ;
		return $result;
	}

	protected function store($action, $key, $value, $expires = Cache::EXPIRES_MEDIUM)
	{
        /** @var Profiling $profiling */
        $profiling = Profiling::create(array('cache', 'redis'))->begin();
		$this->ensureTriedToConnect();

		switch ($action) {
			case 'set':
			case 'replace':
			case 'add':
				try {
					$result = $this->redis->set($key, $value);
					$this->redis->expire($key, $expires);
                    $profiling
                        ->setInfo($action . ' ' . $key)
                        ->end()
                    ;
					return $result;
				} catch (RedisException $e) {
					return $this->alive = false;
				}

			default:
				throw new UnimplementedFeatureException();
		}
	}

	protected function ensureTriedToConnect()
	{
		if ($this->triedConnect)
			return $this;

		$this->triedConnect = true;

		$this->redis = new Redis();

		try {
			$this->redis->pconnect($this->host, $this->port, $this->timeout);
			$this->isAlive();
		} catch (RedisException $e) {
			$this->alive = false;
		}

		return $this;
	}
}
