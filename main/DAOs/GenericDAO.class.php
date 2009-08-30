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

	/**
	 * Basis of all DAO's.
	 * 
	 * @ingroup DAOs
	**/
	abstract class GenericDAO extends Singleton implements BaseDAO
	{
		// override later, BC, <0.9
		protected $mapping = array();

		protected $identityMap	= array();
		
		protected $selectHead	= null;
		
		abstract public function getTable();
		abstract public function getObjectName();
		
		abstract protected function makeObject(&$array, $prefix = null);
		
		public function createObject()
		{
			$className = $this->getObjectName();
			
			return new $className;
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
		 * @return SelectQuery
		**/
		public function makeSelectHead()
		{
			if (null === $this->selectHead) {
				$table = $this->getTable();
				
				$this->selectHead =
					OSQL::select()->
					from($table);
				
				foreach ($this->getFields() as $field)
					$this->selectHead->get(new DBField($field, $table));
			}
			
			return clone $this->selectHead;
		}
		
		/// @deprecated by ComplexBuilderDAO::getMapping()
		public function getMapping()
		{
			if (!$this->mapping)
				throw new WrongStateException('empty mapping');
			
			return $this->mapping;
		}
		
		/// @deprecated by ComplexBuilderDAO::getFields()
		public function getFields()
		{
			static $fields = array();
			
			$name = $this->getObjectName();
			
			if (!isset($fields[$name])) {
				foreach ($this->getMapping() as $property => $field)
					$fields[$name][] = $field === null ? $property : $field;
			}
			
			return $fields[$name];
		}
		
		/// boring delegates
		//@{
		public function get(ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE)
		{
			return Cache::worker($this)->get($oq, $expires);
		}

		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			Assert::isScalar($id);
			Assert::isNotEmpty($id);
			
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
		
		public function getList(ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE)
		{
			return Cache::worker($this)->getList($oq, $expires);
		}
		
		public function getListByIds(
			/* array */ $ids, $expires = Cache::EXPIRES_MEDIUM
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
				
				return array_merge($mapped, $list);
			}
			
			return $mapped;
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
		
		public function getPlainList($expires = Cache::EXPIRES_MEDIUM)
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
		
		public function getCountedList(
			ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getCountedList($oq, $expires);
		}
		
		public function getQueryResult(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getQueryResult($query, $expires);
		}
		
		public function cacheById(
			Identifiable $object, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			return Cache::worker($this)->cacheById($object, $expires);
		}
		
		public function cacheByQuery(
			SelectQuery $query,
			/* Identifiable */ $object,
			$expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->cacheByQuery($query, $object, $expires);
		}
		
		public function cacheListByQuery(SelectQuery $query, /* array */ $array)
		{
			return Cache::worker($this)->cacheListByQuery($query, $array);
		}
		
		public function getCachedById($id)
		{
			return Cache::worker($this)->getCachedById($id);
		}
		
		public function getCachedByQuery(SelectQuery $query)
		{
			return Cache::worker($this)->getCachedByQuery($query);
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
		
		public function dropByIds(/* array */ $ids)
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
		
		public function uncacheByIds($ids)
		{
			foreach ($ids as $id)
				unset($this->identityMap[$id]);
			
			return Cache::worker($this)->uncacheByIds($ids);
		}
		
		public function uncacheByQuery(SelectQuery $query)
		{
			return Cache::worker($this)->uncacheByQuery($query);
		}
		
		public function uncacheLists()
		{
			$this->dropIdentityMap();
			
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
		
		/* void */ protected function checkObjectType(Identifiable $object)
		{
			Assert::isTrue(
				get_class($object) === $this->getObjectName(),
				'strange object given, i can not inject it'
			);
		}
		
		private function addObjectToMap(Identifiable $object)
		{
			if (isset($this->identityMap[$id = $object->getId()]))
				return $this->identityMap[$id];
			else
				return $this->identityMap[$id] = $object;
		}
		
		private function addObjectListToMap($list)
		{
			foreach ($list as &$object) {
				if (isset($this->identityMap[$id = $object->getId()]))
					$object = $this->identityMap[$id];
				else
					$this->identityMap[$id] = $object;
			}
			
			return $list;
		}
	}
?>