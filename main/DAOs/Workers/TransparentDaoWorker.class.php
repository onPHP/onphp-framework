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
		public function getById($id)
		{
			$object = $this->getCachedById($id);
			
			if ($object) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $this->dao->fetchEncapsulants($object);
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

				// expires argument will be ignored by our cachers
				if ($object = $this->cachedFetchObject($query, null, true)) {
					return $object;
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
					return $this->dao->fetchEncapsulants($object);
				
			} else {
				// expires argument will be ignored by our cachers
				if ($object = $this->cachedFetchObject($query, null, false))
					return $object;
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
		public function getCustomList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
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
		
		public function getCustomRowList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
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
		
		public function uncacheByIds($ids)
		{
			foreach ($ids as $id)
				parent::uncacheById($id);
			
			return $this->dao->uncacheLists();
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