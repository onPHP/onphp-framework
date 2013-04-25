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
		$this->ensureTriedToConnect();

		try {
			$this->redis->flushDB();
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
		$this->ensureTriedToConnect();

		try {
			return $this->redis->append($key, $data);
		} catch (RedisException $e) {
			return $this->alive = false;
		}
	}

	public function decrement($key, $value)
	{
		$this->ensureTriedToConnect();

		try {
			return $this->redis->decrBy($key, $value);
		} catch (RedisException $e) {
			return null;
		}
	}

	public function delete($key)
	{
		$this->ensureTriedToConnect();

		try {
			return $this->redis->delete($key);
		} catch (RedisException $e) {
			return $this->alive = false;
		}
	}

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
		return $this->redis->info();
	}

	public function select($db) {
		$this->ensureTriedToConnect();

		if( is_null($db) || !Assert::checkInteger($db) ) {
			throw new WrongArgumentException('DB id should be an integer');
		}
		$result = $this->redis->select($db);
		if( !$result ) {
			throw new WrongStateException('could not change db');
		}
		return $result;
	}

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
