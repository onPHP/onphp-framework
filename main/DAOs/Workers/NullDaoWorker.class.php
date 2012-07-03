<?php
/***************************************************************************
 *   Copyright (C) 2005-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Cacheless DAO worker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for transparent one.
	 * 
	 * @ingroup DAOs
	**/
	class NullDaoWorker extends CommonDaoWorker
	{
		/// single object getters
		//@{
		public function getById($id)
		{
			return parent::getById($id, Cache::DO_NOT_CACHE);
		}
		
		public function getByLogic(LogicalObject $logic)
		{
			return parent::getByLogic($logic, Cache::DO_NOT_CACHE);
		}
		
		public function getByQuery(SelectQuery $query)
		{
			return parent::getByQuery($query, Cache::DO_NOT_CACHE);
		}
		
		public function getCustom(SelectQuery $query)
		{
			return parent::getCustom($query, Cache::DO_NOT_CACHE);
		}
		//@}
		
		/// object's list getters
		//@{
		public function getListByIds(array $ids)
		{
			try {
				return
					$this->getListByLogic(
						Expression::in(
							new DBField(
								$this->dao->getIdName(),
								$this->dao->getTable()
							),
							$ids
						)
					);
			} catch (ObjectNotFoundException $e) {
				return array();
			}
		}
		
		public function getListByQuery(SelectQuery $query)
		{
			return parent::getListByQuery($query, Cache::DO_NOT_CACHE);
		}
		
		public function getListByLogic(LogicalObject $logic)
		{
			return parent::getListByLogic($logic, Cache::DO_NOT_CACHE);
		}
		
		public function getPlainList()
		{
			return parent::getPlainList(Cache::DO_NOT_CACHE);
		}
		//@}
		
		/// custom list getters
		//@{
		public function getCustomList(SelectQuery $query)
		{
			return parent::getCustomList($query, Cache::DO_NOT_CACHE);
		}
		
		public function getCustomRowList(SelectQuery $query)
		{
			return parent::getCustomRowList($query, Cache::DO_NOT_CACHE);
		}
		//@}
		
		/// query result getters
		//@{
		public function getQueryResult(SelectQuery $query)
		{
			return parent::getQueryResult($query, Cache::DO_NOT_CACHE);
		}
		//@}
		
		/// cachers
		//@{
		protected function cacheById(
			Identifiable $object,
			$expires = Cache::DO_NOT_CACHE
		)
		{
			return $object;
		}
		
		protected function cacheByQuery(
			SelectQuery $query,
			/* Identifiable */ $object,
			$expires = Cache::DO_NOT_CACHE
		)
		{
			return $object;
		}
		//@}
		
		/// uncachers
		//@{
		public function uncacheById($id)
		{
			return true;
		}
		
		/**
		 * @return UncacherNullDaoWorker
		 */
		public function getUncacherById($id) {
			return UncacherNullDaoWorker::create();
		}
		
		public function uncacheByIds($ids)
		{
			return true;
		}
		
		public function uncacheByQuery(SelectQuery $query)
		{
			return true;
		}
		
		public function uncacheLists()
		{
			return true;
		}
		//@}
		
		/// cache getters
		//@{
		public function getCachedById($id)
		{
			return null;
		}
		
		protected function getCachedByQuery(SelectQuery $query)
		{
			return null;
		}
		//@}
	}
?>
