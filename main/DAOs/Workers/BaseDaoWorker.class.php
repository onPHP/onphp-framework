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
	 * @ingroup DAOs
	**/
	abstract class BaseDaoWorker implements BaseDAO
	{
		const SUFFIX_LIST	= '_list_';
		const SUFFIX_INDEX	= '_lists_index_';
		const SUFFIX_QUERY	= '_query_';
		const SUFFIX_RESULT	= '_result_';

		protected $dao = null;
		
		protected $className = null;
		
		public function __construct(GenericDAO $dao)
		{
			$this->dao = $dao;
			
			$this->className = $dao->getObjectName();
		}
		
		/**
		 * @return BaseDaoWorker
		**/
		public function setDao(GenericDAO $dao)
		{
			$this->dao = $dao;
			
			return $this;
		}
		
		/// erasers
		//@{
		public function drop(Identifiable $object)
		{
			return $this->dropById($object->getId());
		}
		
		public function dropById($id)
		{
			$result =
				DBPool::getByDao($this->dao)->queryCount(
					OSQL::delete()->from($this->dao->getTable())->
					where(Expression::eq($this->dao->getIdName(), $id))
				);
			
			$this->dao->uncacheById($id);
			
			return $result;
		}
		
		public function dropByIds(array $ids)
		{
			$result =
				DBPool::getByDao($this->dao)->queryCount(
					OSQL::delete()->from($this->dao->getTable())->
					where(Expression::in($this->dao->getIdName(), $ids))
				);
			
			$this->dao->uncacheByIds($ids);
			
			return $result;
		}
		//@}

		/// uncachers
		//@{
		public function uncacheById($id)
		{
			return
				Cache::me()->mark($this->className)->
					delete($this->className.'_'.$id);
		}
		
		protected function uncacheByQuery(SelectQuery $query)
		{
			return
				Cache::me()->mark($this->className)->
					delete($this->className.self::SUFFIX_QUERY.$query->getId());
		}
		//@}
		
		/// cache getters
		//@{
		public function getCachedById($id)
		{
			return
				Cache::me()->mark($this->className)->
					get($this->className.'_'.$id);
		}
		
		protected function getCachedByQuery(SelectQuery $query)
		{
			return
				Cache::me()->mark($this->className)->
					get($this->className.self::SUFFIX_QUERY.$query->getId());
		}
		//@}
		
		/// fetchers
		//@{
		protected function fetchObject(SelectQuery $query)
		{
			if ($row = DBPool::getByDao($this->dao)->queryRow($query)) {
				return $this->dao->makeObject($row);
			}
			
			return null;
		}
		
		protected function cachedFetchObject(
			SelectQuery $query,
			$expires,
			$byId = true
		)
		{
			if ($row = DBPool::getByDao($this->dao)->queryRow($query)) {
				$object = $this->dao->makeOnlyObject($row);
				
				if ($byId)
					$object = $this->cacheById($object, $expires);
				else
					$object = $this->cacheByQuery($query, $object, $expires);
				
				return $this->dao->completeObject($object);
			}
			
			return null;
		}
		
		protected function fetchList(SelectQuery $query)
		{
			$list = array();
			
			if ($rows = DBPool::getByDao($this->dao)->querySet($query)) {
				$proto = $this->dao->getProtoClass();
				
				$proto->beginPrefetch();
				
				foreach ($rows as $row)
					$list[] = $this->dao->makeObject($row);
				
				$proto->endPrefetch($list);
			}
			
			return $list;
		}
		//@}
	}
?>