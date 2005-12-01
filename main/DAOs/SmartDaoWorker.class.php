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
	 * Transparent caching DAO worker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	**/
	final class SmartDaoWorker extends BaseDaoWorker
	{
		//@{
		// single object getters
		public function get(ObjectQuery $oq)
		{
			return $this->getByQuery($oq->toSelectQuery($this->dao));
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

				if ($object = $db->queryObjectRow($query, $this->dao)) {
					return $this->cacheById($object);
				} else {
					$this->cacheNullById($id);
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
					$query, $this->dao
				);
				
				if ($object)
					return $this->cacheByQuery($query, $object);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
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
		//@}
		
		//@{
		// object's list getters
		public function getList(ObjectQuery $oq)
		{
			return $this->getListByQuery($oq->toSelectQuery($this->dao));
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
					$query, $this->dao
				);
				
				if ($list)
					return $this->cacheListByQuery($query, $list);
				else {
					$this->cacheListByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getListByLogic(LogicalObject $logic)
		{
			return $this->getListByQuery($this->makeSelectHead()->where($logic));
		}
		
		public function getPlainList()
		{
			return $this->getListByQuery($this->makeSelectHead());
		}
		//@}

		//@{
		// custom list getters
		public function getCustomList(
			SelectQuery $query, $expres = Cache::DO_NOT_CACHE
		)
		{
			if ($list = DBFactory::getDefaultInstance()->querySet($query))
				return $list;
			else
				throw new ObjectNotFoundException();
		}
		
		public function getCustomRowList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			if ($list = DBFactory::getDefaultInstance()->queryColumn($query))
				return $list;
			else
				throw new ObjectNotFoundException();
		}
		//@}
		
		//@{
		// query result getters
		public function getCountedList(ObjectQuery $oq)
		{
			return $this->getQueryResult($oq->toSelectQuery($this->dao));
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
				
				$list = $db->queryObjectSet($query, $this->dao);
				
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
		//@}

		//@{
		// erasers
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
			
			$this->uncacheLists();

			return $result;
		}
		//@}
		
		//@{
		// cachers
		public function cacheById(Identifiable $object)
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
		
		public function cacheByQuery(
			SelectQuery $query, /* Identifiable */ $object
		)
		{
			$className = $this->getObjectName();
			$queryId = $query->getId();
			
			$indexKey = $className.self::SUFFIX_INDEX;
			$semKey = $this->keyToInt($indexKey);
			$key = $className.self::SUFFIX_QUERY.$queryId;
			
			$pool = SemaphorePool::me();

			if ($pool->get($semKey)) {
				$this->syncMap($indexKey, $key);
				
				Cache::me()->mark($this->getObjectName())->
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
			$className = $this->getObjectName();
			
			$listKey = $className.self::SUFFIX_LIST.$query->getId();
			$indexKey = $className.self::SUFFIX_INDEX;
			$semKey = $this->keyToInt($indexKey);
			
			$pool = SemaphorePool::me();
			
			if ($pool->get($semKey)) {
			
				$this->syncMap($indexKey, $listKey);
				
				$cache->mark($className)->
					add($listKey, $array, Cache::EXPIRES_FOREVER);
				
				if ($array !== Cache::NOT_FOUND) {
					foreach ($array as $key => $object) {
						$this->cacheById($object);
					}
				}
				
				$pool->free($semKey);
			}

			return $array;
		}
		//@}
		
		//@{
		// uncachers
		public function uncacheById($id)
		{
			$className = $this->getObjectName();
			$objectKey = $className.'_'.$id;
			
			$cache = Cache::me();
			
			$this->uncacheLists();
			
			return $cache->mark($className)->delete($objectKey);
		}
		
		public function uncacheByIds($ids)
		{
			$className = $this->getObjectName();
			$cache = Cache::me();
			
			foreach ($ids as $id)
				$cache->mark($className)->delete(
					$className.'_'.$id
				);
			
			return $this->uncacheLists();
		}
		
		public function uncacheLists()
		{
			$className = $this->getObjectName();
			
			$indexKey = $className.self::SUFFIX_INDEX;
			$intKey	= $this->keyToInt($indexKey);
			
			$cache = Cache::me();
			$pool = SemaphorePool::me();

			$pool->get($intKey);
			
			$indexList = $cache->mark($className)->get($indexKey);

			if ($indexList) {
				$cache->mark($className)->delete($indexKey);
				
				foreach ($indexList as $key => &$true)
					$cache->mark($className)->delete($key);
			}
			
			$pool->free($intKey);
			
			return true;
		}
		//@}
		
		//@{
		// internal helpers
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

		protected function getCachedList(SelectQuery $query)
		{
			$className = $this->getObjectName();
			return
				Cache::me()->mark($className)->
					get($className.self::SUFFIX_LIST.$query->getId());
		}
		
		private function syncMap($mapKey, $objectKey)
		{
			$cache = Cache::me();
			
			if (!$map = $cache->get($mapKey))
				$map = array();
			
			$semKey = $this->keyToInt($mapKey);
			
			$map[$objectKey] = true;
			
			$cache->mark($this->getObjectName())->
				set($mapKey, $map, Cache::EXPIRES_FOREVER);
			
			return true;
		}
		
		private function keyToInt($key)
		{
			return hexdec(substr(md5($key), 0, 6)) + 1;
		}
		//@}
	}
?>