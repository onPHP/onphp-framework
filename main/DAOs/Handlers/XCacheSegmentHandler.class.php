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

	/**
	 * @see http://trac.lighttpd.net/xcache/
	 * 
	 * @ingroup DAOs
	**/
	final class XCacheSegmentHandler extends OptimizerSegmentHandler
	{
		public function __construct($segmentId)
		{
			parent::__construct($segmentId);
			
			$this->locker = SemaphorePool::me();
		}
		
		public function drop()
		{
			return xcache_unset($this->id);
		}
		
		public function ping($key)
		{
			if (xcache_isset($this->id))
				return parent::ping($key);
			else
				return false;
		}
		
		protected function getMap()
		{
			$this->locker->get($this->id);
			
			if (!$map = xcache_get($this->id)) {
				$map = array();
			}
			
			return $map;
		}
		
		protected function storeMap(array $map)
		{
			$result = xcache_set($this->id, $map);
			
			$this->locker->free($this->id);
			
			return $result;
		}
	}
