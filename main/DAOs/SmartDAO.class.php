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

	abstract class SmartDAO extends GenericDAO
	{
		const SUFFIX_MAP	= '_map';
		const SUFFIX_LIST	= '_list_';
		const SUFFIX_INDEX	= '_lists_index';
		const SUFFIX_QUERY	= '_query_';
		const SUFFIX_RESULT	= '_result_';
		
		public function dropByIds($ids)
		{
			$result =
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::in('id', $ids))
				);
			
			foreach ($ids as $id)
				$this->uncacheById($id);
				
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
			$this->dropLists();
			
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
			$className = $this->getObjectName();
			
			return 
				Cache::me()->mark($className)->
					add(
						$className.'_'.$id,
						Cache::NOT_FOUND,
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
			
			$this->syncMap($className.'_'.$queryId.self::SUFFIX_MAP, $key);
			
			Cache::me()->mark($this->getObjectName())->
				add($key, $object, Cache::EXPIRES_FOREVER);
			
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
					
					$mapKey = $className.'_'.$object->getId().self::SUFFIX_MAP;
					
					$this->syncMap($mapKey, $listKey);
					
					$this->cacheObject($object);
	
				}
			}
			
			$cache->mark($className)->
				add($listKey, $array, Cache::EXPIRES_FOREVER);
			
			$this->syncMap($indexKey, $listKey);
			
			return $array;
		}

		protected function uncacheById($id)
		{
			$className = $this->getObjectName();
			$objectKey = $className.'_'.$id;
			$mapKey = $objectKey.self::SUFFIX_MAP;
			
			$cache = Cache::me();
			
			if ($map = $cache->get($mapKey)) {
				
				$indexKey = $className.self::SUFFIX_INDEX;
				
				$sem = sem_get($this->keyToInt($mapKey), 1, 0600, true);
				$indexSem = sem_get($this->keyToInt($indexKey), 1, 0600, true);
				
				Assert::isTrue(sem_acquire($sem) && sem_acquire($indexSem));
				
				if (!$indexList = $cache->mark($className)->get($indexKey)) {
					sem_remove($indexSem);
					$indexSem = null;
					$indexList = array();
				}
				
				foreach ($map as $key => $true) {
					$cache->mark($className)->delete($key);
					unset($indexList[$key]);
				}
				
				sem_remove($sem);

				if ($indexSem) {
					$cache->set($indexKey, $indexList, Cache::EXPIRES_FOREVER);
					sem_remove($indexSem);
				}
			}
			
			return $cache->mark($className)->delete($objectKey);
		}

		private function syncMap($mapKey, $objectKey)
		{
			$cache = Cache::me();
			
			if (!$map = $cache->get($mapKey))
				$map = array();
			
			$sem = sem_get($this->keyToInt($mapKey), 1, 0600, true);
			Assert::isTrue(sem_acquire($sem));
			
			$map[$objectKey] = true;
			
			$cache->mark($this->getObjectName())->
				set($mapKey, $map, Cache::EXPIRES_FOREVER);
			
			sem_remove($sem);
		}
		
		private function keyToInt($key)
		{
			return hexdec(substr(md5($key), 0, 16));
		}
	}
?>