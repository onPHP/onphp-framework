<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Transparent and scalable DAO worker, Jedi's best choice.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for locking-based worker.
	 * @see VoodooDaoWorker for greedy and unscalable one.
	 * 
	 * @ingroup DAOs
	**/
	final class CacheDaoWorker extends TransparentDaoWorker
	{
		const MAX_RANDOM_ID = 134217728;
		
		/// cachers
		//@{
		public function cacheByQuery(
			SelectQuery $query,
			/* Identifiable */ $object,
			$expires = Cache::EXPIRES_FOREVER
		)
		{
			$key =
				$this->className
				.self::SUFFIX_QUERY
				.$query->getId()
				.$this->getLayerId();
			
			Cache::me()->mark($this->className)->
				add($key, $object, $expires);
			
			return $object;
		}
		
		public function cacheListByQuery(
			SelectQuery $query,
			/* array || Cache::NOT_FOUND */ $array
		)
		{
			if ($array !== Cache::NOT_FOUND) {
				Assert::isArray($array);
				Assert::isTrue(current($array) instanceof Identifiable);
			}
			
			$cache = Cache::me();
			
			$key =
				$this->className
				.self::SUFFIX_LIST
				.$query->getId()
				.$this->getLayerId();
			
			$cache->mark($this->className)->
				add($key, $array, Cache::EXPIRES_FOREVER);
			
			return $array;
		}
		//@}
		
		/// uncachers
		//@{
		public function uncacheLists()
		{
			if (
				!Cache::me()->
					mark($this->className)->
					increment($this->className, 1)
			)
				Cache::me()->mark($this->className)->delete($this->className);
			
			return true;
		}
		//@}
		
		/// internal helper
		//@{
		protected function gentlyGetByKey($key)
		{
			return Cache::me()->mark($this->className)->get(
				$key.$this->getLayerId()
			);
		}
		
		private function getLayerId()
		{
			if (
				!$result =
					Cache::me()->mark($this->className)->get($this->className)
			) {
				$result = mt_rand(1, self::MAX_RANDOM_ID);
				
				Cache::me()->
				mark($this->className)->
				set(
					$this->className,
					$result,
					Cache::EXPIRES_FOREVER
				);
			}
			
			return '@'.$result;
		}
		//@}
	}
?>