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
		const SUFFIX_RESULT	= '_result_';
		
		public function dropByIds($ids)
		{
			foreach ($ids as $id)
				$this->uncacheById($id);

			return
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::in('id', $ids))
				);
		}

		public function dropById($id)
		{
			$this->uncacheById($id);
			
			return
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::eq('id', $id))
				);
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
		
		public function get(ObjectQuery $oq)
		{
			return $this->getByQuery($oq->toSelectQuery($this));
		}
		
		public function getById($id)
		{
			if ($object = $this->getCachedById($id))
				return $object;
			else {
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
					throw new ObjectNotFoundException(
						"there is no such object for '".$this->getObjectName()
						."' with query == {$query->toString($db->getDialect())}"
					);
				}
			}
		}
		
		public function getListByIds($ids, $expires = Cache::EXPIRES_MEDIUM)
		{
			$list = array();
			
			foreach ($ids as $id) {
				try {
					$list[] = $this->getById($id, $expires);
				} catch (ObjectNotFoundException $e) {
					// ignore
				}
			}

			return $list;
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
			if ($object = $this->getCachedByQuery($query))
				return $object;
			elseif (
				$object = DBFactory::getDefaultInstance()->queryObjectRow(
					$query, $this
				)
			) {
				return $this->cacheObjectByQuery($query, $object);
			} else
				throw new ObjectNotFoundException();
		}
		
		public function getList(ObjectQuery $oq)
		{
			return $this->getListByQuery($oq->toSelectQuery($this));
		}
		
		public function getCountedList(ObjectQuery $oq)
		{
			return $this->getQueryResult($oq->toSelectQuery($this));
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
			if ($list = $this->getCachedList($query))
				return $list;
			elseif (
				$list = DBFactory::getDefaultInstance()->queryObjectSet(
					$query, $this
				)
			)
				return $this->cacheList($query, $list);
			else
				throw new ObjectNotFoundException();
		}
		
		public function getQueryResult(SelectQuery $query)
		{
			$db = DBFactory::getDefaultInstance();

			$className = $this->getObjectName();
			
			$countKey = $className.self::SUFFIX_RESULT.$query->getId();
			
			$cache = Cache::me();
			
			$res = new QueryResult();
			
			if ($list = $this->getCachedList($query)) {
				return
					$res->
						setList($list)->
						setCount(
							$cache->mark($className)->
								get($countKey)
						)->
						setQuery($query);
			} elseif ($list = $db->queryObjectSet($query, $this)) {
				$count = clone $query;
			
				$count =
					$db->queryRow(
						$count->dropFields()->dropOrder()->limit(null, null)->
						get(SQLFunction::create('COUNT', '*')->setAlias('count'))
					);

				$cache->mark($className)->
					set(
						$countKey,
						$count['count'],
						Cache::EXPIRES_FOREVER
					);

				return
					$res->
						setList($list)->
						setCount($count['count'])->
						setQuery($query);
			} else
				throw new ObjectNotFoundException();
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
		
		protected function getCachedByQuery(SelectQuery $query)
		{
			$className = $this->getObjectName();
			
			return
				Cache::me()->mark($className)->
					get($className.'_query_'.$query->getId());
		}

		protected function cacheObjectByQuery(
			SelectQuery $query, Identifiable $object
		)
		{
			$className = $this->getObjectName();
			
			$key = $className.'_query_'.$query->getId();
			
			$this->syncMap($className.'_'.$object->getId().self::SUFFIX_MAP, $key);
			
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
			Assert::isArray($array);
			Assert::isTrue(current($array) instanceof Identifiable);
			
			$cache = Cache::me();
			$className = $this->getObjectName();
			
			$listKey = $className.self::SUFFIX_LIST.$query->getId();
			$indexKey = $className.self::SUFFIX_INDEX;
			
			foreach ($array as $key => $object) {
				
				$mapKey = $className.'_'.$object->getId().self::SUFFIX_MAP;
				
				$this->syncMap($mapKey, $listKey);
				
				$this->cacheObject($object);
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