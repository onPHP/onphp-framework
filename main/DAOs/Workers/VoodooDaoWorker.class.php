<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Transparent though quite obscure and greedy DAO worker.
	 * 
	 * @warning Do not ever think about using it on production systems, unless
	 * you're fully understand every line of code here.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for less obscure, but locking-based worker.
	 * @see FileSystemDaoWorker for filesystem based child.
	 * 
	 * @ingroup DAOs
	**/
	class VoodooDaoWorker extends TransparentDaoWorker
	{
		protected $classKey = null;
		
		private static $handlerName = null;
		
		public function __construct(GenericDAO $dao)
		{
			parent::__construct($dao);

			if (($cache = Cache::me()) instanceof WatermarkedPeer)
				$watermark = $cache->getWatermark();
			else
				$watermark = null;
			
			$this->classKey = $this->keyToInt($watermark.$this->className);
			
			if (!self::$handlerName) {
				if (!extension_loaded('sysvshm')) {
					if (extension_loaded('eaccelerator')) {
						self::$handlerName = 'eAcceleratorSegmentHandler';
					} elseif (extension_loaded('apc')) {
						self::$handlerName = 'ApcSegmentHandler';
					} elseif (extension_loaded('xcache')) {
						self::$handlerName = 'XCacheSegmentHandler';
					} else {
						throw new UnsupportedMethodException(
							'can not find suitable segment handler'
						);
					}
				} else {
					self::$handlerName = 'SharedMemorySegmentHandler';
				}
			}
			
			$this->handler = new self::$handlerName($this->classKey);
		}
		
		//@{
		// cachers
		public function cacheByQuery(
			SelectQuery $query, /* Identifiable */ $object
		)
		{
			$queryId = $query->getId();
			
			$key = $this->className.self::SUFFIX_QUERY.$queryId;
			
			if ($this->handler->touch($this->keyToInt($key, 15)))
				Cache::me()->mark($this->className)->
					add($key, $object, Cache::EXPIRES_FOREVER);
			
			return $object;
		}
		
		public function cacheListByQuery(SelectQuery $query, /* array */ $array)
		{
			if ($array !== Cache::NOT_FOUND) {
				Assert::isArray($array);
				Assert::isTrue(current($array) instanceof Identifiable);
			}
			
			$cache = Cache::me();
			
			$key = $this->className.self::SUFFIX_LIST.$query->getId();
			
			if ($this->handler->touch($this->keyToInt($key, 15))) {
				
				$cache->mark($this->className)->
					add($key, $array, Cache::EXPIRES_FOREVER);
				
				if ($array !== Cache::NOT_FOUND)
					foreach ($array as $key => $object) {
						if (
							!$this->handler->ping(
								$this->keyToInt(
									$this->className.'_'.$object->getId(), 15
								)
							)
						) {
							$this->cacheById($object);
						}
					}
			}

			return $array;
		}
		//@}

		//@{
		// uncachers
		public function uncacheLists()
		{
			return $this->handler->drop();
		}
		//@}
		
		//@{
		// internal helpers
		protected function gentlyGetByKey($key)
		{
			if ($this->handler->ping($this->keyToInt($key, 15)))
				return Cache::me()->mark($this->className)->get($key);
			else {
				Cache::me()->mark($this->className)->delete($key);
				return null;
			}
		}
		//@}
	}
?>