<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Basis of all DAO's.
	 * 
	 * @ingroup DAOs
	**/
	abstract class GenericDAO extends Singleton implements BaseDAO
	{
		private $identityMap	= array();
		
		abstract public function getTable();
		abstract public function getObjectName();
		
		abstract public function makeOnlyObject($array, $prefix = null);
		abstract public function completeObject(
			Identifiable $object, $array = null, $prefix = null
		);
		
		public function makeObject($array, $prefix = null)
		{
			if (isset($this->identityMap[$array[$prefix.$this->getIdName()]]))
				return $this->identityMap[$array[$prefix.$this->getIdName()]];
			
			return $this->addObjectToMap(
				$this->completeObject(
					$this->makeOnlyObject($array, $prefix),
					$array,
					$prefix
				)
			);
		}
		
		/**
		 * Returns link name which is used to get actual DB-link from DBPool,
		 * returning null by default for single-source projects.
		 * 
		 * @see DBPool
		**/
		public function getLinkName()
		{
			return null;
		}
		
		public function getIdName()
		{
			return 'id';
		}
		
		public function getSequence()
		{
			return $this->getTable().'_id';
		}
		
		/**
		 * @return AbstractProtoClass
		**/
		public function getProtoClass()
		{
			return call_user_func(array($this->getObjectName(), 'proto'));
		}
		
		public function getMapping()
		{
			return $this->getProtoClass()->getMapping();
		}
		
		public function getFields()
		{
			static $fields = array();
			
			$className = $this->getObjectName();
			
			if (!isset($fields[$className])) {
				$fields[$className] = array_values($this->getMapping());
			}
			
			return $fields[$className];
		}
		
		/**
		 * @return SelectQuery
		**/
		public function makeSelectHead()
		{
			static $selectHead = null;
			
			if (!$selectHead) {
				$table = $this->getTable();
				
				$selectHead =
					OSQL::select()->
					from($table);
				
				foreach ($this->getFields() as $field)
					$selectHead->get(new DBField($field, $table));
			}
			
			return clone $selectHead;
		}
		
		/// boring delegates
		//@{
		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			if (isset($this->identityMap[$id]))
				return $this->identityMap[$id];
			
			return $this->addObjectToMap(
				Cache::worker($this)->getById($id, $expires)
			);
		}
		
		public function getByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return $this->addObjectToMap(
				Cache::worker($this)->getByLogic($logic, $expires)
			);
		}
		
		public function getByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return $this->addObjectToMap(
				Cache::worker($this)->getByQuery($query, $expires)
			);
		}
		
		public function getCustom(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getCustom($query, $expires);
		}
		
		public function getListByIds(
			array $ids, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$mapped = $remain = array();
			
			foreach ($ids as $id) {
				if (isset($this->identityMap[$id])) {
					$mapped[] = $this->identityMap[$id];
				} else {
					$remain[] = $id;
				}
			}
			
			if ($remain) {
				$list = $this->addObjectListToMap(
					Cache::worker($this)->getListByIds($remain, $expires)
				);
				
				$mapped = $mapped + $list;
			}
			
			return ArrayUtils::regularizeList($ids, $mapped);
		}
		
		public function getListByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return $this->addObjectListToMap(
				Cache::worker($this)->getListByQuery($query, $expires)
			);
		}
		
		public function getListByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return $this->addObjectListToMap(
				Cache::worker($this)->getListByLogic($logic, $expires)
			);
		}
		
		public function getPlainList($expires = Cache::DO_NOT_CACHE)
		{
			return $this->addObjectListToMap(
				Cache::worker($this)->getPlainList($expires)
			);
		}
		
		public function getCustomList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getCustomList($query, $expires);
		}
		
		public function getCustomRowList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getCustomRowList($query, $expires);
		}
		
		public function getQueryResult(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getQueryResult($query, $expires);
		}
		
		public function drop(Identifiable $object)
		{
			$this->checkObjectType($object);
			
			return $this->dropById($object->getId());
		}
		
		public function dropById($id)
		{
			unset($this->identityMap[$id]);
			
			return Cache::worker($this)->dropById($id);
		}
		
		public function dropByIds(array $ids)
		{
			foreach ($ids as $id)
				unset($this->identityMap[$id]);
			
			return Cache::worker($this)->dropByIds($ids);
		}
		
		public function uncacheById($id)
		{
			unset($this->identityMap[$id]);
			
			return Cache::worker($this)->uncacheById($id);
		}
		
		public function uncacheByIds(array $ids)
		{
			foreach ($ids as $id)
				unset($this->identityMap[$id]);
			
			return Cache::worker($this)->uncacheByIds($ids);
		}
		
		public function uncacheLists()
		{
			$this->identityMap = array();
			
			return Cache::worker($this)->uncacheLists();
		}
		//@}
		
		/**
		 * @return GenericDAO
		**/
		public function dropIdentityMap()
		{
			$this->identityMap = array();
			
			return $this;
		}
		
		protected function inject(
			InsertOrUpdateQuery $query,
			Identifiable $object
		)
		{
			$this->checkObjectType($object);
			
			return $this->doInject(
				$this->setQueryFields(
					$query->setTable($this->getTable()), $object
				),
				$object
			);
		}
		
		protected function doInject(
			InsertOrUpdateQuery $query,
			Identifiable $object
		)
		{
			$db = DBPool::getByDao($this);
			
			if (!$db->isQueueActive()) {
				$count = $db->queryCount($query);
				
				$this->uncacheById($object->getId());
				
				if ($count !== 1)
					throw new WrongStateException(
						$count.' rows affected: racy or insane inject happened: '
						.$query->toDialectString($db->getDialect())
					);
			} else {
				$db->queryNull($query);
				
				$this->uncacheById($object->getId());
			}
			
			// clean out Identifier, if any
			return $this->addObjectToMap($object->setId($object->getId()));
		}
		
		/* void */ protected function checkObjectType(Identifiable $object)
		{
			Assert::isTrue(
				get_class($object) === $this->getObjectName(),
				'strange object given, i can not inject it'
			);
		}
		
		private function addObjectToMap(Identifiable $object)
		{
			return $this->identityMap[$object->getId()] = $object;
		}
		
		private function addObjectListToMap($list)
		{
			foreach ($list as $object)
				$this->identityMap[$object->getId()] = $object;
			
			return $list;
		}
	}
?>