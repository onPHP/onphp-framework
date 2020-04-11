<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\DAO\Handlers;

use OnPHP\Core\Cache\SemaphorePool;
use OnPHP\Core\Cache\Cache;

/**
 * @see http://pecl.php.net/package/APC
 * 
 * @ingroup DAO
**/
final class ApcSegmentHandler extends OptimizerSegmentHandler
{
	public function __construct($segmentId)
	{
		parent::__construct($segmentId);

		$this->locker = SemaphorePool::me();
	}

	public function drop()
	{
		return apc_delete($this->id);
	}

	protected function getMap()
	{
		$this->locker->get($this->id);

		if (!$map = apc_fetch($this->id)) {
			$map = array();
		}

		return $map;
	}

	protected function storeMap(array $map)
	{
		$result = apc_store($this->id, $map, Cache::EXPIRES_FOREVER);

		$this->locker->free($this->id);

		return $result;
	}
}
?>