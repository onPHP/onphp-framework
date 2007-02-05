<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Basis of all DAO's.
	 * 
	 * @ingroup DAOs
	**/
	abstract class GenericDAO extends Singleton implements BaseDAO, Serializable
	{
		// override later
		protected $mapping = array();
		
		protected $identityMap	= array();
		
		protected $link			= null;
		protected $selectHead	= null;
		
		abstract protected function makeObject(&$array, $prefix = null);
		
		/**
		 * Returns link name which is used to get actual db-link from DBPool,
		 * returning null by default for single-source projects.
		 * 
		 * @see DBPool
		**/
		public function getLinkName()
		{
			return null;
		}
		
		public function getMapping()
		{
			return $this->mapping;
		}
		
		public function getFieldFor($property)
		{
			if (array_key_exists($property, $this->mapping)) {
				return
					$this->mapping[$property] === null
						? $property
						: $this->mapping[$property];
			}
			
			throw new MissingElementException('unknown property '.$property);
		}
		
		/**
		 * @return SelectQuery
		**/
		public function makeSelectHead()
		{
			if (null === $this->selectHead) {
				if (!$this->mapping)
					throw new WrongStateException('empty mapping');
				
				$table = $this->getTable();

				$this->selectHead = 
					OSQL::select()->
					from($table);
				
				foreach ($this->getFields() as $field)
					$this->selectHead->get(new DBField($field, $table));
			}
			
			return clone $this->selectHead;
		}
		
		public function getFields()
		{
			static $fields = null;
			
			if (!$fields) {
				foreach ($this->getMapping() as $property => $field)
					$fields[] = $field === null ? $property : $field;
			}
			
			return $fields;
		}
		
		/// boring delegates
		//@{
		public function get(ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE)
		{
			return Cache::worker($this)->get($oq, $expires);
		}

		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			if (isset($this->identityMap[$id]))
				return $this->identityMap[$id];
			
			return Cache::worker($this)->getById($id, $expires);
		}

		public function getByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getByLogic($logic, $expires);
		}

		public function getByQuery(
			SelectQuery $query, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			return Cache::worker($this)->getByQuery($query, $expires);
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
			return Cache::worker($this)->getListByIds($ids, $expires);
		}
		
		public function getListByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getListByQuery($query, $expires);
		}
		
		public function getListByCriteria(
			Criteria $criteria, $expires = Cache::DO_NOT_CACHE)
		{
			return Cache::worker($this)->getListByCriteria($criteria, $expires);
		}
		
		public function getListByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getListByLogic($logic, $expires);
		}
		
		public function getPlainList($expires = Cache::EXPIRES_MEDIUM)
		{
			return Cache::worker($this)->getPlainList($expires);
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
			
			return Cache::worker($this)->dropById($object->getId());
		}
		
		public function dropById($id)
		{
			return Cache::worker($this)->dropById($id);
		}
		
		public function dropByIds(/* array */ $ids)
		{
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
			return Cache::worker($this)->uncacheLists();
		}
		//@}
		
		/// prevents serialization of inner worker and identity map
		//@{
		public function __sleep()
		{
			return array();
		}
		
		public function serialize()
		{
			return "";
		}
		
		public function unserialize($stuff) {/*_*/}
		//@}
		
		/* void */ protected function checkObjectType(Identifiable $object)
		{
			Assert::isTrue(
				get_class($object) === $this->getObjectName(),
				'strange object given, i can not inject it'
			);
		}
	}
?>