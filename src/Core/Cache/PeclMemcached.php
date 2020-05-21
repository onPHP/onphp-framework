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

namespace OnPHP\Core\Cache;

use OnPHP\Core\Base\Assert;

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
	const DEFAULT_PORT			= 11211;
	const DEFAULT_HOST			= '127.0.0.1';
	const DEFAULT_WEIGHT		= 1;

	const DEFAULT_CONNECT_TIMEOUT	= 1000;
	
	private $instance		= null;
	private $resultCode 	= \Memcached::RES_SUCCESS;
	private $requestTimeout = null;
	
	/**
	 * @param string $host - ip sddress
	 * @param string $port
	 * @param int $weight
	 * @param int $connectTimeout - milliseconds
	 * @return \OnPHP\Core\Cache\PeclMemcached
	 */
	public static function create(
		$host = self::DEFAULT_HOST,
		$port = self::DEFAULT_PORT,
		$weight = self::DEFAULT_WEIGHT,
		$connectTimeout = self::DEFAULT_CONNECT_TIMEOUT
	)
	{
		return new self($host, $port, $weight, $connectTimeout);
	}

	/**
	 * @param string $host - ip sddress
	 * @param string $port
	 * @param int $weight
	 * @param int $connectTimeout - milliseconds
	 */
	public function __construct(
		$host = self::DEFAULT_HOST,
		$port = self::DEFAULT_PORT,
		$weight = self::DEFAULT_WEIGHT,
		$connectTimeout = self::DEFAULT_CONNECT_TIMEOUT
	)
	{	
		$this->instance = new \Memcached();
		
		if ($connectTimeout != self::DEFAULT_CONNECT_TIMEOUT) {
			$this->instance->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $connectTimeout);
		}

		if ($this->compress == false) {
			$this->instance->setOption(\Memcached::OPT_COMPRESSION, false);
		}

		$this->instance->addServer($host, $port, $weight);
		
		$this->alive = $this->instance->set(self::class, time());
	}

	public function __destruct()
	{
		if ($this->alive) {
			$this->instance->quit();
		}
	}

	/**
	 * @param int $option
	 * @param mixed $value
	 * @return \OnPHP\Core\Cache\PeclMemcached
	 */
	public function setOption($option, $value)
	{
		$this->instance->setOption($option, $value);
		
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::clean()
	 * @return \OnPHP\Core\Cache\PeclMemcached
	 */
	public function clean($delay = 0)
	{
		Assert::isPositiveInteger($delay);
		
		$this->instance->flush($delay);
		
		$this->processResultCode();
		
		return parent::clean();
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::increment()
	 */
	public function increment($key, int $value = 1)
	{
		$result = $this->instance->increment($key, $value);
		
		$this->processResultCode();
		
		return $result;
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::decrement()
	 */
	public function decrement($key, int $value = 1)
	{
		$result = $this->instance->decrement($key, $value);
		
		$this->processResultCode();
		
		return $result;
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::getList()
	 */
	public function getList($indexes)
	{
		$list = $this->instance->getMulti($indexes);
		
		$this->processResultCode();
		
		return ($list !== false && count($list) > 0) ? $list : null;
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::get()
	 */
	public function get($index)
	{
		$result = $this->instance->get($index);
		
		$this->processResultCode();

		if ($result === false 
			&& $this->instance->getResultCode() != \Memcached::RES_SUCCESS
		) {
			return null;
		}
		
		return $result;
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::delete()
	 */
	public function delete($index)
	{
		// second parameter required, wrt new memcached protocol:
		// delete key 0 (see process_delete_command in the memcached.c)
		// Warning: it is workaround!
		$result = $this->instance->delete($index, 0);
		
		$this->processResultCode();
		
		return $result;
	}

	/**
	 * {@inheritDoc}
	 * @see \OnPHP\Core\Cache\CachePeer::append()
	 */
	public function append($key, $value)
	{
		$result = $this->instance->append($key, $value);
		
		$this->processResultCode();
		
		return $result;
	}

	/**
	 * @param float $requestTimeout time in miliseconds
	 * @return PeclMemcached
	 */
	public function setTimeout($requestTimeout) {
		$this->requestTimeout = $requestTimeout;
		
		$this->instance->setOptions([
			\Memcached::OPT_SEND_TIMEOUT => $requestTimeout,
			\Memcached::OPT_RECV_TIMEOUT => $requestTimeout,
		]);

		return $this;
	}

	/**
	 * @return float
	 */
	public function getTimeout() {
		return $this->requestTimeout;
	}
	
	/**
	 * @return number
	 */
	public function getResultCode()
	{
		return $this->resultCode;
	}

	protected function store(
		$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
	)
	{
		$result =
			$this->instance->$action(
				$key,
				$value,
				$expires
			);
		
		$this->processResultCode();
		
		return $result;
	}
	
	/**
	 * @see https://www.php.net/manual/ru/memcached.getresultcode.php
	 */
	protected function processResultCode()
	{
		$this->resultCode = $this->instance->getResultCode();
		
		switch ($this->resultCode) {
			case \Memcached::RES_SUCCESS:
				/* All is ok. */
				$this->alive = true;
				break;
				
			case \Memcached::RES_SERVER_TEMPORARILY_DISABLED:
			case \Memcached::RES_TIMEOUT:
			case \Memcached::RES_FAILURE:
			case \Memcached::RES_HOST_LOOKUP_FAILURE:
				$this->alive = false;
				break;
				
			case \Memcached::RES_UNKNOWN_READ_FAILURE:
			case \Memcached::RES_SERVER_ERROR:
			case \Memcached::RES_NO_SERVERS:
				/* Does it mean that server is not alive? */
				break;
				
			default:
				/* Not interesting */
				break;
		}
	}
}
?>