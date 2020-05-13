<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Cache;

use OnPHP\Core\Exception\BaseException;
use OnPHP\Core\Base\Assert;

/**
 * Connector for PECL's Memcache extension by Antony Dovgal.
 *
 * @see http://tony2001.phpclub.net/
 * @see http://pecl.php.net/package/memcache
 *
 * @ingroup Cache
**/
class PeclMemcache extends CachePeer
{
	const DEFAULT_PORT		= 11211;
	const DEFAULT_HOST		= '127.0.0.1';

	private $instance = null;

	/**
	 * @return PeclMemcache
	**/
	public static function create(
		$host = self::DEFAULT_HOST,
		$port = self::DEFAULT_PORT
	)
	{
		return new self($host, $port);
	}

	public function __construct(
		$host = self::DEFAULT_HOST,
		$port = self::DEFAULT_PORT
	)
	{
		$this->instance = new \Memcache();

		try {
			try {
				$this->instance->pconnect($host, $port);
			} catch (BaseException $e) {
				$this->instance->connect($host, $port);
			}

			$this->alive = true;
		} catch (BaseException $e) {
			// bad luck.
		}
	}

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
	 * @return PeclMemcached
	**/
	public function clean()
	{
		try {
			$this->instance->flush();
		} catch (BaseException $e) {
			$this->alive = false;
		}

		return parent::clean();
	}

	public function increment($key, int $value = 1)
	{
		try {
			return $this->instance->increment($key, $value);
		} catch (BaseException $e) {
			return null;
		}
	}

	public function decrement($key, int $value = 1)
	{
		try {
			return $this->instance->decrement($key, $value);
		} catch (BaseException $e) {
			return null;
		}
	}

	public function getList($indexes)
	{
		return
			($return = $this->get($indexes))
				? $return
				: null;
	}

	public function get($index)
	{
                $flag = null;
		try {
			$result = $this->instance->get($index, $flag);
                        return
                                $result === false && is_null($flag)
                                        ? null
                                        : $result;
		} catch (BaseException $e) {
			if(strpos($e->getMessage(), 'Invalid key') !== false)
				return null;

			$this->alive = false;

			return null;
		}

		Assert::isUnreachable();
	}

	public function delete($index)
	{
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

	public function append($key, $data)
	{
		try {
			return $this->instance->append($key, $data);
		} catch (BaseException $e) {
			return $this->alive = false;
		}

		Assert::isUnreachable();
	}

	protected function store(
		$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
	)
	{
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
}
?>
