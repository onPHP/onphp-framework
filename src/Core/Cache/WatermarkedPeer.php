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

/**
 * Watermark's all cache activity to avoid namespace collisions.
 * 
 * @ingroup Cache
**/
final class WatermarkedPeer extends SelectivePeer
{
	private $peer		= null;
	private $originalWatermark = null;
	private $watermark	= null;

	/// map class -> watermark
	private $map		= null;

	/**
	 * @return WatermarkedPeer
	**/
	public static function create(
		CachePeer $peer,
		$watermark = "Single onPHP's project"
	)
	{
		return new self($peer, $watermark);
	}

	public function __construct(
		CachePeer $peer,
		$watermark = "Single onPHP's project"
	)
	{
		$this->peer = $peer;
		$this->setWatermark($watermark);
	}

	public function setWatermark($watermark)
	{
		$this->originalWatermark = $watermark;
		$this->watermark = md5($watermark.' ['.ONPHP_VERSION.']::');

		return $this;
	}

	public function getWatermark()
	{
		return $this->watermark;
	}

	public function getActualWatermark()
	{
		if (
			$this->className
			&& isset($this->map[$this->className])
		)
			return $this->map[$this->className];

		return $this->watermark;
	}

	/**
	 * associative array, className -> watermark
	 * 
	 * @return WatermarkedPeer
	**/
	public function setClassMap($map)
	{
		$this->map = array();

		foreach ($map as $className => $watermark)
			$this->map[$className] = md5($watermark.' ['.ONPHP_VERSION.']::');

		return $this;
	}

	/**
	 * @return CachePeer
	**/
	public function mark($className)
	{
		$this->className = $className;

		$this->peer->mark($this->getActualWatermark().$className);

		return $this;
	}

	public function increment($key, int $value = 1)
	{
		return $this->peer->increment(
			$this->getActualWatermark().$key,
			$value
		);
	}

	public function decrement($key, int $value = 1)
	{
		return $this->peer->decrement(
			$this->getActualWatermark().$key,
			$value
		);
	}

	public function getList($indexes)
	{
		$peerIndexMap = array();
		foreach ($indexes as $index)
			$peerIndexMap[$this->getActualWatermark().$index] = $index;

		$peerIndexes = array_keys($peerIndexMap);
		$peerResult = $this->peer->getList($peerIndexes);

		$result = array();
		if (!empty($peerResult)) {
			foreach ($peerResult as $key => $value) {
				$result[$peerIndexMap[$key]] = $value;
			}
		} else {
			$result = $peerResult;
		}

		return $result;
	}

	public function get($key)
	{
		return $this->peer->get($this->getActualWatermark().$key);
	}

	public function delete($key)
	{
		return $this->peer->delete($this->getActualWatermark().$key);
	}

	/**
	 * @return CachePeer
	**/
	public function clean()
	{
		$this->peer->clean();

		return parent::clean();
	}

	public function isAlive()
	{
		return $this->peer->isAlive();
	}

	public function append($key, $data)
	{
		return $this->peer->append($this->getActualWatermark().$key, $data);
	}

	/**
	 * @return CachePeer
	 */
	public function getRuntimeCopy()
	{
		$newWm = new WatermarkedPeer(
			$this->peer->getRuntimeCopy(),
			$this->originalWatermark
		);
		$newWm->map = $this->map;
		return $newWm;
	}

	protected function store(
		$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
	)
	{
		return
			$this->peer->$action(
				$this->getActualWatermark().$key, $value, $expires
			);
	}
}
?>