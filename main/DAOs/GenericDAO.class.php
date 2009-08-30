<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Basis of all DAO's.
	 * 
	 * @ingroup DAOs
	**/
	abstract class GenericDAO extends Singleton implements BaseDAO
	{
		protected $link			= null;
		protected $selectHead	= null;
		
		// backwards compatibility
		protected $fields = array();
		
		abstract public function getTable();
		abstract public function getObjectName();

		/**
		 * Builds complete object.
		 * 
		 * @see http://onphp.org/examples.DAOs.en.html
		 * 
		 * @param $array	associative array('fieldName' => 'value')
		 * @param $prefix	prefix (if any) of all fieldNames
		**/
		abstract protected function makeObject(
			/* array */ &$array,
			$prefix = null
		);

		public function getFields()
		{
			return $this->fields;
		}
		
		public function getSequence()
		{
			return $this->getTable().'_id';
		}
		
		/**
		 * Returns link name which is used to get actual db-link from DBPool,
		 * returning null by default for backwards compatibility.
		 * 
		 * @see DBPool
		**/
		public function getLinkName()
		{
			return null;
		}

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
		
		//@{
		// boring delegates
		public function get(ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE)
		{
			return Cache::worker($this)->get($oq, $expires);
		}

		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			return Cache::worker($this)->getById($id, $expires);
		}

		public function getByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return Cache::worker($this)->getByLogic($logic, $expires);
		}

		public function getByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
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
			return Cache::worker($this)->uncacheById($id);
		}
		
		public function uncacheByIds($ids)
		{
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
	}
?>