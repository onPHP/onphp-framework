<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Transparent though quite obscure and greedy DAO worker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for less obscure, but locking-based worker.
	 * 
	 * @ingroup DAOs
	**/
	final class VoodooDaoWorker extends TransparentDaoWorker
	{
		const SEGMENT_SIZE = 2097152; // 2 ^ 21
		
		private $classKey = null;
		
		public function __construct(GenericDAO $dao)
		{
			parent::__construct($dao);
			
			$this->classKey = $this->keyToInt($this->className);
		}
		
		//@{
		// cachers
		public function cacheByQuery(
			SelectQuery $query, /* Identifiable */ $object
		)
		{
			$queryId = $query->getId();
			
			$key = $this->className.self::SUFFIX_QUERY.$queryId;
			
			if ($this->touch($key))
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
			
			if ($this->touch($key)) {
				
				$cache->mark($this->className)->
					add($key, $array, Cache::EXPIRES_FOREVER);
				
				if ($array !== Cache::NOT_FOUND)
					foreach ($array as $key => $object) {
						if (!$this->ping($this->className.'_'.$object->getId()))
							$this->cacheById($object);
					}
			}

			return $array;
		}
		//@}

		//@{
		// uncachers
		public function uncacheLists()
		{
			try {
				$shm = shm_attach($this->classKey, self::SEGMENT_SIZE, 0600);
			} catch (BaseException $e) {
				return false;
			}
			
			$result = shm_remove($shm);
			
			shm_detach($shm);
			
			return $result;
		}
		//@}
		
		//@{
		// internal helpers
		protected function gentlyGetByKey($key)
		{
			if ($this->ping($key))
				return Cache::me()->mark($this->className)->get($key);
			else {
				Cache::me()->mark($this->className)->delete($key);
				return null;
			}
		}
		
		private function touch($key)
		{
			try {
				$shm = shm_attach($this->classKey, self::SEGMENT_SIZE, 0600);
			} catch (BaseException $e) {
				return false;
			}

			try {
				$result = shm_put_var($shm, $this->keyToInt($key, 15), true);
				shm_detach($shm);
			} catch (BaseException $e) {
				// not enough shared memory left, rotate it.
				shm_detach($shm);
				return $this->uncacheLists();
			}
			
			return $result;
		}
		
		private function unlink($key)
		{
			try {
				$shm = shm_attach($this->classKey, self::SEGMENT_SIZE, 0600);
			} catch (BaseException $e) {
				return false;
			}
			
			try {
				$result = shm_remove_var($shm, $this->keyToInt($key, 15));
				shm_detach($shm);
				return $result;
			} catch (BaseException $e) {
				// non existent key
				shm_detach($shm);
				return false;
			}
			
			/* NOTREACHED */
		}
		
		private function ping($key)
		{
			try {
				$shm = shm_attach($this->classKey, self::SEGMENT_SIZE, 0600);
			} catch (BaseException $e) {
				return false;
			}
			
			try {
				$result = shm_get_var($shm, $this->keyToInt($key, 15));
			} catch (BaseException $e) {
				// variable key N doesn't exist, bleh
				shm_detach($shm);
				return false;
			}
			
			shm_detach($shm);
			
			return $result;
		}
		//@}
	}
?>