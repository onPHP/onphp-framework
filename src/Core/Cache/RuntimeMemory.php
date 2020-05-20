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

namespace OnPHP\Core\Cache;

/**
 * Default process RAM cache.
 * 
 * @ingroup Cache
**/
final class RuntimeMemory extends CachePeer
{
	private $cache = array();

	/**
	 * @return RuntimeMemory
	**/
	public static function create()
	{
		return new self;
	}

	public function isAlive()
	{
		return true;
	}

	public function increment($key, int $value = 1)
	{
		if (isset($this->cache[$key]))
			return $this->cache[$key] += $value;

		return null;
	}

	public function decrement($key, int $value = 1)
	{
		if (isset($this->cache[$key]))
			return $this->cache[$key] -= $value;

		return null;
	}

	public function get($key)
	{
		if (isset($this->cache[$key]))
			return $this->cache[$key];

		return null;
	}

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

	public function append($key, $data)
	{
		if (isset($this->cache[$key])) {
			$this->cache[$key] .= $data;
			return true;
		}

		return false;
	}

	protected function store($action, $key, $value, $expires = 0)
	{
		if ($action == 'add' && isset($this->cache[$key]))
			return false;
		elseif ($action == 'replace' && !isset($this->cache[$key]))
			return false;

		if (is_object($value))
			$this->cache[$key] = clone $value;
		else
			$this->cache[$key] = $value;

		return true;
	}
}
?>