<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Basis for transparent DAO workers.
	 * 
	 * @see VoodooDaoWorker for obscure and greedy worker.
	 * @see SmartDaoWorker for less obscure locking-based worker.
	 * 
	 * @ingroup DAOs
	**/
	abstract class TransparentDaoWorker extends CommonDaoWorker
	{
		abstract protected function gentlyGetByKey($key);
		
		/// single object getters
		//@{
		public function getById($id)
		{
			try {
				return parent::getById($id, Cache::EXPIRES_FOREVER);
			} catch (ObjectNotFoundException $e) {
				$this->cacheNullById($id);
				throw $e;
			}
		}
		
		public function getByLogic(LogicalObject $logic)
		{
			return parent::getByLogic($logic, Cache::EXPIRES_FOREVER);
		}
		
		public function getByQuery(SelectQuery $query)
		{
			try {
				return parent::getByQuery($query, Cache::EXPIRES_FOREVER);
			} catch (ObjectNotFoundException $e) {
				$this->cacheByQuery($query, Cache::NOT_FOUND);
				throw $e;
			}
		}
		
		public function getCustom(SelectQuery $query)
		{
			try {
				return parent::getCustom($query, Cache::EXPIRES_FOREVER);
			} catch (ObjectNotFoundException $e) {
				$this->cacheByQuery($query, Cache::NOT_FOUND);
				throw $e;
			}
		}
		//@}
		
		/// object's list getters
		//@{
		public function getListByIds(array $ids)
		{
			$list = array();
			$toFetch = array();
			
			if ($cachedList = Cache::me()->getList($ids)) {
				foreach ($cachedList as $cached) {
					if (
						($cached === Cache::NOT_FOUND)
						|| !$cached
					) {
						$toFetch[] = $cached->getId();
					} else {
						$list[] = $this->dao->fetchEncapsulants($cached);
					}
				}
			} else {
				$toFetch = $ids;
			}
			
			if ($toFetch) {
				foreach ($toFetch as $id) {
					try {
						$list[] = $this->getById($id);
					} catch (ObjectNotFoundException $e) {/*_*/}
				}
			}
			
			return $list;
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
				if ($list = $this->fetchList($query))
					return $this->cacheListByQuery($query, $list);
				else {
					$this->cacheListByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
			
			Assert::isUnreachable();
		}
		
		public function getListByLogic(LogicalObject $logic)
		{
			return parent::getListByLogic($logic, Cache::EXPIRES_FOREVER);
		}
		
		public function getPlainList()
		{
			return parent::getPlainList(Cache::EXPIRES_FOREVER);
		}
		//@}
		
		/// custom list getters
		//@{
		public function getCustomList(SelectQuery $query)
		{
			try {
				return parent::getCustomList($query, Cache::EXPIRES_FOREVER);
			} catch (ObjectNotFoundException $e) {
				$this->cacheByQuery($query, Cache::NOT_FOUND);
				throw $e;
			}
		}
		
		public function getCustomRowList(SelectQuery $query)
		{
			try {
				return parent::getCustomRowList($query, Cache::EXPIRES_FOREVER);
			} catch (ObjectNotFoundException $e) {
				$this->cacheByQuery($query, Cache::NOT_FOUND);
				throw $e;
			}
		}
		//@}
		
		/// query result getters
		//@{
		public function getQueryResult(SelectQuery $query)
		{
			return parent::getQueryResult($query, Cache::EXPIRES_FOREVER);
		}
		//@}

		/// cachers
		//@{
		protected function cacheById(Identifiable $object)
		{
			Cache::me()->mark($this->className)->
				add(
					$this->className.'_'.$object->getId(),
					$object,
					Cache::EXPIRES_FOREVER
				);
			
			return $object;
		}
		//@}
		
		/// uncachers
		//@{
		public function uncacheById($id)
		{
			$this->dao->uncacheLists();

			return parent::uncacheById($id);
		}
		//@}
		
		/// internal helpers
		//@{
		protected function getCachedByQuery(SelectQuery $query)
		{
			return
				$this->gentlyGetByKey(
					$this->className.self::SUFFIX_QUERY.$query->getId()
				);
		}
		
		protected function getCachedList(SelectQuery $query)
		{
			return
				$this->gentlyGetByKey(
					$this->className.self::SUFFIX_LIST.$query->getId()
				);
		}
		
		protected function cacheNullById($id)
		{
			return
				Cache::me()->mark($this->className)->
					add(
						$this->className.'_'.$id,
						Cache::NOT_FOUND,
						Cache::EXPIRES_FOREVER
					);
		}
		
		protected function keyToInt($key)
		{
			static $precision = null;
			
			if (!$precision) {
				$precision = strlen(dechex(PHP_INT_MAX)) - 1;
			}
			
			return hexdec(substr(md5($key), 0, $precision)) + strlen($key);
		}
		//@}
	}
?>