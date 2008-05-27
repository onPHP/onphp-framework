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
	abstract class TransparentDaoWorker extends BaseDaoWorker
	{
		abstract protected function gentlyGetByKey($key);
		
		/// single object getters
		//@{
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
				$query =
					$this->dao->makeSelectHead()->
					andWhere(
						Expression::eq(
							DBField::create(
								$this->dao->getIdName(),
								$this->dao->getTable()
							),
							$id
						)
					);

				if ($object = $this->fetchObject($query)) {
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
					$this->dao->makeSelectHead()->andWhere($logic)
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
				if ($object = $this->fetchObject($query))
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
				$custom = DBPool::getByDao($this->dao)->queryRow($query);
				
				if ($custom)
					return $this->cacheByQuery($query, $custom);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		//@}
		
		/// object's list getters
		//@{
		public function getList(ObjectQuery $oq)
		{
			return $this->getListByQuery($oq->toSelectQuery($this->dao));
		}
		
		public function getListByIds($ids)
		{
			$list = array();
			$toFetch = array();
			$prefixed = array();
			
			foreach ($ids as $key => $id)
				$prefixed[$key] = $this->className.'_'.$id;
			
			if (
				$cachedList
					= Cache::me()->mark($this->className)->getList($prefixed)
			) {
				foreach ($cachedList as $key => $cached) {
					if ($cached !== Cache::NOT_FOUND) {
						if ($cached)
							$list[] = $cached;
						else
							$toFetch[] = $ids[$key];
					}
				}
			} else {
				$toFetch = $ids;
			}
			
			if (!$toFetch)
				return $list;
			
			try {
				return
					array_merge(
						$list,
						$this->getListByLogic(
							Expression::in($this->dao->getIdName(), $toFetch)
						)
					);
			} catch (ObjectNotFoundException $e) {
				// nothing to fetch
				return $list;
			}
			
			Assert::isUnreachable();
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
			return $this->getListByQuery(
				$this->dao->makeSelectHead()->andWhere($logic)
			);
		}
		
		public function getPlainList()
		{
			return $this->getListByQuery(
				$this->dao->makeSelectHead()
			);
		}
		//@}

		/// custom list getters
		//@{
		public function getCustomList(SelectQuery $query)
		{
			$list = $this->getCachedByQuery($query);
			
			if ($list) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $list;
			} else {
				$list = DBPool::getByDao($this->dao)->querySet($query);
				
				if ($list)
					return $this->cacheByQuery($query, $list);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
			
			Assert::isUnreachable();
		}
		
		public function getCustomRowList(SelectQuery $query)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			$list = $this->getCachedByQuery($query);
			
			if ($list) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $list;
			} else {
				$list = DBPool::getByDao($this->dao)->queryColumn($query);
				
				if ($list)
					return $this->cacheByQuery($query, $list);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
			
			Assert::isUnreachable();
		}
		//@}
		
		/// query result getters
		//@{
		public function getCountedList(ObjectQuery $oq)
		{
			return $this->getQueryResult($oq->toSelectQuery($this->dao));
		}
		
		public function getQueryResult(SelectQuery $query)
		{
			$db = DBPool::getByDao($this->dao);

			$result = $this->getCachedByQuery($query);
			
			if ($result) {
				return $result;
			} else {
				$list = $this->fetchList($query);
				
				$count = clone $query;
				
				$count =
					$db->queryRow(
						$count->dropFields()->dropOrder()->limit(null, null)->
						get(SQLFunction::create('COUNT', '*')->setAlias('count'))
					);
				
				return
					$this->cacheByQuery(
						$query,
						
						$list
							?
								QueryResult::create()->
								setList($list)->
								setCount($count['count'])->
								setQuery($query)
							:
								QueryResult::create()
					);
			}
		}
		//@}

		/// cachers
		//@{
		public function cacheById(Identifiable $object)
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
		
		public function uncacheByIds($ids)
		{
			foreach ($ids as $id)
				parent::uncacheById($id);
			
			return $this->dao->uncacheLists();
		}
		//@}
		
		/// internal helpers
		//@{
		public function getCachedByQuery(SelectQuery $query)
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
			static $null = Cache::NOT_FOUND;
			
			return
				Cache::me()->mark($this->className)->
					add(
						$this->className.'_'.$id,
						$null,
						Cache::EXPIRES_FOREVER
					);
		}
		
		protected function keyToInt($key)
		{
			// 7 == strlen(dechex(x86 PHP_INT_MAX)) - 1
			return hexdec(substr(md5($key), 0, 7)) + strlen($key);
		}
		//@}
	}
?>