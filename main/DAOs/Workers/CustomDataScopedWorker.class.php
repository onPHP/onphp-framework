<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Cache custom scoped data
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for transparent one.
	 * 
	 * @ingroup DAOs
	**/
	final class CustomDataScopedWorker extends CacheDaoWorker
	{
		public function __construct($dao)
		{
			$this->dao = $dao;
			
			$this->className = $dao->getObjectName();
			
			if (($cache = Cache::me()) instanceof WatermarkedPeer)
				$this->watermark =
					$cache->mark($this->className)->getActualWatermark();
		}
		
		public function cacheData(
			$key,
			$data,
			$expires = Cache::EXPIRES_FOREVER
		)
		{
			Cache::me()->mark($this->className)->
				add(
					$this->makeDataKey($key, self::SUFFIX_QUERY),
					$data,
					$expires
				);
			
			return $data;
		}
		
		public function getCachedData($key)
		{
			return
				Cache::me()->mark($this->className)->
					get($this->makeDataKey($key, self::SUFFIX_QUERY));
		}
		
		private function makeDataKey($key, $suffix)
		{
			return
				$this->className
				.$suffix
				.$key
				.$this->watermark;
		}
	}
?>