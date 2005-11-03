<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Transparent caching DAO.
	 * 
	 * @see SmartDAO for manual-caching one.
	**/
	abstract class SmartDAO extends GenericDAO
	{
		const SUFFIX_LIST	= '_list_';
		const SUFFIX_INDEX	= '_lists_index';
		const SUFFIX_QUERY	= '_query_';
		const SUFFIX_RESULT	= '_result_';
		
		public function dropByIds($ids)
		{
			$className = $this->getObjectName();

			$cache = Cache::me();
			
			$result =
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::in('id', $ids))
				);
			
			foreach ($ids as $id)
				$cache->mark($className)->delete($className.'_'.$id);
			
			$this->dropLists();

			return $result;
		}

		public function dropById($id)
		{
			$result =
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::eq('id', $id))
				);
			
			$this->uncacheById($id);
			
			return $result;
		}
		
		public function dropLists()
		{
			$className = $this->getObjectName();
			
			$indexKey = $className.self::SUFFIX_INDEX;
			
			$cache = Cache::me();
			
			$indexList = $cache->mark($className)->get($indexKey);

			if ($indexList) {
				$cache->mark($className)->delete($indexKey);
				
				foreach ($indexList as $key => &$true)
					$cache->mark($className)->delete($key);
			}
			
			return true;
		}
		
		public function getCachedById($id)
		{
			$className = $this->getObjectName();
			
			return Cache::me()->mark($className)->get($className.'_'.$id);
		}
		
		public function getById($id)
		{
			$object = $this->getCachedById($id);
			
			if ($object) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $object;
			} else {
				$db = DBFactory::getDefaultInstance();

				$query = 
					$this->makeSelectHead()->
					where(
						Expression::eq(
							DBField::create('id', $this->getTable()),
							$id
						)
					);

				if ($object = $db->queryObjectRow($query, $this)) {
					return $this->cacheObject($object);
				} else {
					$this->cacheNullById($id);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getListByIds($ids)
		{
			$list = array();
			$toFetch = array();
			
			foreach ($ids as $id) {
				if (!$list[] = $this->getCachedById($id))
					$toFetch[] = $id;
			}
			
			if (!$toFetch)
				return $list;
			
			try {
				return
					array_merge(
						$list,
						$this->getListByLogic(
							Expression::in('id', $toFetch)
						)
					);
			} catch (ObjectNotFoundException $e) {
				foreach ($toFetch as $id) {
					try {
						$list[] = $this->getById($id);
					} catch (ObjectNotFoundException $e) {
						// ignore
					}
				}

				return $list;
			}
			
			/* NOTREACHED */
		}
		
		public function getCustom(SelectQuery $query)
		{
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);

			$custom = $this->getCachedByQuery($query);
			
			if ($custom) {
				if ($custom === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $custom;
			} else {
				$custom = DBFactory::getDefaultInstance()->queryRow($query);
				
				if ($custom)
					return $this->cacheByQuery($query, $custom);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getCustomList(SelectQuery $query)
		{
			$custom = $this->getCachedByQuery($query);
			
			if ($custom) {
				if ($custom === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $custom;
			} else {
				$custom = DBFactory::getDefaultInstance()->querySet($query);
				
				if ($custom)
					return $this->cacheByQuery($query, $custom);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}

		public function getByLogic(LogicalObject $logic)
		{
			return
				$this->getByQuery(
					$this->makeSelectHead()->where($logic)
				);
		}

		public function getByQuery(SelectQuery $query)
		{
			$object = $this->getCachedByQuery($query);
			
			if ($object) {
				
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $object;
				
			} else {
				$object = DBFactory::getDefaultInstance()->queryObjectRow(
					$query, $this
				);
				
				if ($object)
					return $this->cacheByQuery($query, $object);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getPlainList()
		{
			return $this->getListByQuery($this->makeSelectHead());
		}
		
		public function getListByLogic(LogicalObject $logic)
		{
			return $this->getListByQuery($this->makeSelectHead()->where($logic));
		}

		public function getListByQuery(SelectQuery $query)
		{
			$list = $this->getCachedList($query);
			
			if ($list) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $list;
			} else {
				$list = DBFactory::getDefaultInstance()->queryObjectSet(
					$query, $this
				);
				
				if ($list)
					return $this->cacheList($query, $list);
				else {
					$this->cacheList($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getQueryResult(SelectQuery $query)
		{
			$db = DBFactory::getDefaultInstance();

			$className = $this->getObjectName();
			
			$cache = Cache::me();
			
			$res = new QueryResult();
			
			$result = $this->getCachedByQuery($query);
			
			if ($result) {
				
				if ($result === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $result;

			} else {
				
				$list = $db->queryObjectSet($query, $this);
				
				$count = clone $query;
			
				$count =
					$db->queryRow(
						$count->dropFields()->dropOrder()->limit(null, null)->
						get(SQLFunction::create('COUNT', '*')->setAlias('count'))
					);

				if (!$list) {
					$list = Cache::NOT_FOUND;
					
					$this->cacheByQuery($query, $list);
					
					throw new ObjectNotFoundException();
				} else {
					return
						$this->cacheByQuery(
							$query,
							$res->
								setList($list)->
								setCount($count['count'])->
								setQuery($query)
						);
				}
			}
		}

		public function uncacheById($id)
		{
			$className = $this->getObjectName();
			$objectKey = $className.'_'.$id;
			
			$cache = Cache::me();
			
			$this->dropLists();
			
			return $cache->mark($className)->delete($objectKey);
		}

		protected function cacheObject(Identifiable $object)
		{
			$className = $this->getObjectName();
			
			Cache::me()->mark($className)->
				add(
					$className.'_'.$object->getId(),
					$object,
					Cache::EXPIRES_FOREVER
				);
			
			return $object;
		}
		
		protected function cacheNullById($id)
		{
			static $null = Cache::NOT_FOUND;
			
			$className = $this->getObjectName();
			
			return 
				Cache::me()->mark($className)->
					add(
						$className.'_'.$id,
						$null,
						Cache::EXPIRES_FOREVER
					);
		}
		
		protected function getCachedByQuery(SelectQuery $query)
		{
			$className = $this->getObjectName();
			
			return
				Cache::me()->mark($className)->
					get($className.self::SUFFIX_QUERY.$query->getId());
		}

		protected function cacheByQuery(
			SelectQuery $query, /* Identifiable */ $object
		)
		{
			$className = $this->getObjectName();
			$queryId = $query->getId();
			$key = $className.self::SUFFIX_QUERY.$queryId;
			
			try {
				$this->syncMap($className.self::SUFFIX_INDEX, $key);
			
				Cache::me()->mark($this->getObjectName())->
					add($key, $object, Cache::EXPIRES_FOREVER);
			} catch (BaseException $e) {
				// failed to acquire semaphore
			}
			
			return $object;
		}
		
		protected function getCachedList(SelectQuery $query)
		{
			$className = $this->getObjectName();
			return
				Cache::me()->mark($className)->
					get($className.self::SUFFIX_LIST.$query->getId());
		}
		
		protected function cacheList(SelectQuery $query, /* array */ $array)
		{
			if ($array !== Cache::NOT_FOUND) {
				Assert::isArray($array);
				Assert::isTrue(current($array) instanceof Identifiable);
			}
			
			$cache = Cache::me();
			$className = $this->getObjectName();
			
			$listKey = $className.self::SUFFIX_LIST.$query->getId();
			$indexKey = $className.self::SUFFIX_INDEX;
			
			if ($array !== Cache::NOT_FOUND) {
				foreach ($array as $key => $object) {
					$this->cacheObject($object);
				}
			}

			try {
				$this->syncMap($indexKey, $listKey);
				
				$cache->mark($className)->
					add($listKey, $array, Cache::EXPIRES_FOREVER);
			} catch (BaseException $e) {
				// failed to acquire semaphore
			}
			
			return $array;
		}

		private function syncMap($mapKey, $objectKey)
		{
			$cache = Cache::me();
			static $semaphores = array();
			
			if (!$map = $cache->get($mapKey))
				$map = array();
			$semKey = $this->keyToInt($mapKey);
			
			if (! isset($semaphores[$semKey])) {
				$sem = sem_get($semKey, 1, 0644, true);
				$semaphores[$semKey] = $sem;
			}
			
			sem_acquire($semaphores[$semKey]);
			
			$map[$objectKey] = true;
			
			$cache->mark($this->getObjectName())->
				set($mapKey, $map, Cache::EXPIRES_FOREVER);
			
			sem_release($semaphores[$semKey]);
		}
		
		private function keyToInt($key)
		{
			return hexdec(substr(md5($key), 0, 7));
		}
	}
?>