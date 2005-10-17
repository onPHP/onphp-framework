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

	abstract class SmartDAO extends Singletone
	{
		protected $selectHead = null;

		abstract public function getTable();
		abstract public function getObjectName();
		
		abstract protected function makeObject(&$array, $prefix = null);

		public function getFields()
		{
			return $this->fields;
		}
		
		public function getSequence()
		{
			return $this->getTable().'_id';
		}

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
		
		public function getCachedById($id)
		{
			$className = $this->getObjectName();
			
			return Cache::me()->mark($className)->get($className.'_'.$id);
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
			$db = DBFactory::getDefaultInstance();
			
			if ($object = $this->getCachedByQuery($query))
				return $object;
			elseif ($object = $db->queryObjectRow($query, $this)) {
				return $this->cacheObjectByQuery($query, $object);
			} else
				throw new ObjectNotFoundException(
					"there is no such object for '".$this->getObjectName()
					."' with query == {$query->toString($db->getDialect())}"
				);
		}
		
		public function getListByQuery(SelectQuery $query)
		{
			$db = DBFactory::getDefaultInstance();
			
			if ($list = $this->getCachedList($query))
				return $list;
			elseif ($list = $db->queryObjectSet($query, $this))
				return $this->cacheList($query, $list);
			else
				throw new ObjectNotFoundException(
					'zero list for query such query - '
					.$query->toString($db->getDialect())
				);
		}

		public function getListByLogic(LogicalObject $logic)
		{
			return $this->getListByQuery($this->makeSelectHead()->where($logic));
		}

		/**
		 * default makeSelectHead's behaviour
		**/
		public function makeSelectHead()
		{
			if (null === $this->selectHead) {
				$this->selectHead = 
					OSQL::select()->
					from($this->getTable());
				
				$table = $this->getTable();
				
				foreach ($this->getFields() as $field)
					$this->selectHead->get(new DBField($field, $table));
			}
			
			return clone $this->selectHead;
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
			
			$this->syncMap($className.'_'.$object->getId().'_map', $key);
			
			Cache::me()->mark($this->getObjectName())->
				add($key, $object, Cache::EXPIRES_FOREVER);
			
			return $this;
		}
		
		protected function getCachedList(SelectQuery $query)
		{
			$className = $this->getObjectName();
			
			return
				Cache::me()->mark($className)->
					get($className.'_ist_'.$query->getId());
		}
		
		protected function cacheList(SelectQuery $query, /* array */ $array)
		{
			Assert::isArray($array);
			Assert::isTrue(current($array) instanceof Identifiable);
			
			$cache = Cache::me();
			$className = $this->getObjectName();
			
			$listKey = $className.'_list_'.$query->getId();
			
			foreach ($array as $key => $object) {
				
				$mapKey = $className.'_'.$object->getId().'_map';
				
				$this->syncMap($mapKey, $listKey);
				
				$this->cacheObject($object);
			}
			
			$cache->mark($className)->
				add($listKey, $array, Cache::EXPIRES_FOREVER);
			
			return $array;
		}

		protected function uncacheById($id)
		{
			$className = $this->getObjectName();
			$objectKey = $className.'_'.$id;
			$mapKey = $objectKey.'_map';
			
			$cache = Cache::me();
			
			if ($map = $cache->get($mapKey)) {
				$sem = sem_get($mapKey, 1, 0600, true);
				Assert::isTrue(sem_acquire($sem));
				
				foreach ($map as $key => $true)
					$cache->mark($className)->drop($key);
				
				sem_release($sem);
			}
			
			return $cache->mark($className)->drop($objectKey);
		}

		private function syncMap($mapKey, $objectKey)
		{
			if (!$map = $cache->get($mapKey))
				$map = array();
			
			$sem = sem_get($mapKey, 1, 0600, true);
			Assert::isTrue(sem_acquire($sem));
			
			$map[$objectKey] = true;
			
			$cache->mark($this->getObjectName())->
				replace($mapKey, $map, Cache::EXPIRES_FOREVER);
			
			sem_release($sem);
		}
	}
?>