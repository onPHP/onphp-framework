<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Transparent caching DAO worker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see VoodooDaoWorker for greedy though non-blocking brother.
	 * 
	 * @ingroup DAOs
	**/
	final class SmartDaoWorker extends TransparentDaoWorker
	{
		private $indexKey = null;
		
		public function __construct(GenericDAO $dao)
		{
			parent::__construct($dao);
			
			$this->indexKey = $this->className.self::SUFFIX_INDEX;
		}
		
		//@{
		// cachers
		public function cacheByQuery(
			SelectQuery $query, /* Identifiable */ $object
		)
		{
			$queryId = $query->getId();
			
			$semKey = $this->keyToInt($this->indexKey);
			
			$key = $this->className.self::SUFFIX_QUERY.$queryId;
			
			$pool = SemaphorePool::me();

			if ($pool->get($semKey)) {
				$this->syncMap($key);
				
				Cache::me()->mark($this->className)->
					add($key, $object, Cache::EXPIRES_FOREVER);
				
				$pool->free($semKey);
			}
			
			return $object;
		}
		
		public function cacheListByQuery(SelectQuery $query, /* array */ $array)
		{
			if ($array !== Cache::NOT_FOUND) {
				Assert::isArray($array);
				Assert::isTrue(current($array) instanceof Identifiable);
			}
			
			$cache = Cache::me();
			
			$listKey = $this->className.self::SUFFIX_LIST.$query->getId();
			
			$semKey = $this->keyToInt($this->indexKey);
			
			$pool = SemaphorePool::me();
			
			if ($pool->get($semKey)) {
			
				$this->syncMap($listKey);
				
				$cache->mark($this->className)->
					add($listKey, $array, Cache::EXPIRES_FOREVER);
				
				if ($array !== Cache::NOT_FOUND)
					foreach ($array as $key => $object)
						$this->cacheById($object);
				
				$pool->free($semKey);
			}

			return $array;
		}
		//@}
		
		//@{
		// uncachers
		public function uncacheLists()
		{
			$intKey	= $this->keyToInt($this->indexKey);
			
			$cache = Cache::me();
			$pool = SemaphorePool::me();
			
			if ($pool->get($intKey)) {
				$indexList = $cache->mark($this->className)->get($this->indexKey);
				$cache->mark($this->className)->delete($this->indexKey);
	
				if ($indexList) {
					foreach ($indexList as $key => &$true)
						$cache->mark($this->className)->delete($key);
				}
				
				$pool->free($intKey);
				
				return true;
			}
			
			$cache->mark($this->className)->delete($this->indexKey);
			
			return false;
		}
		//@}
		
		//@{
		// internal helpers
		protected function gentlyGetByKey($key)
		{
			if ($object = Cache::me()->mark($this->className)->get($key)) {
				if ($this->checkMap($key)) {
					return $object;
				} else {
					Cache::me()->mark($this->className)->delete($key);
				}
			}
			
			return null;
		}
		
		private function syncMap($objectKey)
		{
			$cache = Cache::me();
			
			if (!$map = $cache->mark($this->className)->get($this->indexKey))
				$map = array();
			
			$semKey = $this->keyToInt($this->indexKey);
			
			$map[$objectKey] = true;
			
			$cache->mark($this->className)->
				set($this->indexKey, $map, Cache::EXPIRES_FOREVER);
			
			return true;
		}
		
		private function checkMap($objectKey)
		{
			$pool = SemaphorePool::me();
			
			$semKey = $this->keyToInt($this->indexKey);
			
			if (!$pool->get($semKey))
				return false;
			
			if (!$map = Cache::me()->get($this->indexKey)) {
				$pool->free($semKey);
				return false;
			}
			
			if (!isset($map[$objectKey])) {
				$pool->free($semKey);
				return false;
			}
			
			$pool->free($semKey);
			
			return true;
		}
		//@}
	}
?>