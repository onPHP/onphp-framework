<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Tunable (aka manual) caching DAO worker.
	 * 
	 * @see SmartDaoWorker for auto-caching one.
	 * 
	 * @ingroup DAOs
	**/
	final class CommonDaoWorker extends BaseDaoWorker
	{
		//@{
		// single object getters
		public function get(ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE)
		{
			return $this->getByQuery($oq->toSelectQuery($this->dao), $expires);
		}

		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($object = $this->getCachedById($id))
			) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $object;
			} else {
				$db = DBPool::getByDao($this->dao);
				
				$query =
					$this->dao->
						makeSelectHead()->
						andWhere(
							Expression::eq(
								DBField::create('id', $this->dao->getTable()),
								$id
							)
						);
				
				if ($object = $db->queryObjectRow($query, $this->dao)) {
					return
						$expires === Cache::DO_NOT_CACHE
							? $object
							: $this->cacheById($object, $expires);
				} else {
					throw new ObjectNotFoundException(
						"there is no such object for '".get_class($this->dao)
						."' with query == {$query->toDialectString($db->getDialect())}"
					);
				}
			}
		}

		public function getByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return
				$this->getByQuery(
					$this->dao->makeSelectHead()->andWhere($logic), $expires
				);
		}

		public function getByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			$db = DBPool::getByDao($this->dao);
			
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($object = $this->getCachedByQuery($query))
			) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $object;
			} elseif ($object = $db->queryObjectRow($query, $this->dao)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheByQuery($query, $object, $expires);
			} else
				throw new ObjectNotFoundException(
					"there is no such object for '".get_class($this->dao)
					."' with query == {$query->toDialectString($db->getDialect())}"
				);
		}
		
		public function getCustom(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			$db = DBPool::getByDao($this->dao);
		
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);
		
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($object = $this->getCachedByQuery($query))
			) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $object;
			} elseif ($object = $db->queryRow($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheByQuery($query, $object, $expires);
			} else {
				throw new ObjectNotFoundException(
					"zero for query == {$query->toDialectString($db->getDialect())}"
				);
			}
		}
		//@}

		//@{
		// object's list getters
		public function getList(ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE)
		{
			return
				$this->getListByQuery(
					$oq->toSelectQuery($this->dao), $expires
				);
		}
		
		public function getListByIds($ids, $expires = Cache::EXPIRES_MEDIUM)
		{
			if ($expires !== Cache::DO_NOT_CACHE) {
				
				$list = array();
				$toFetch = array();

				foreach ($ids as $id) {
					$cached = $this->getCachedById($id);
					
					if ($cached !== Cache::NOT_FOUND) {
						if ($cached)
							$list[] = $cached;
						else
							$toFetch[] = $id;
					}
				}
				
				if (!$toFetch)
					return $list;
				
				try {
					return
						array_merge(
							$list,
							$this->getListByLogic(
								Expression::in(
									new DBField('id', $this->dao->getTable()),
									$toFetch
								),
								$expires
							)
						);
				} catch (ObjectNotFoundException $e) {
					foreach ($toFetch as $id) {
						try {
							$list[] = $this->getById($id, $expires);
						} catch (ObjectNotFoundException $e) {
							// ignore
						}
					}

					return $list;
				}
	
				/* NOTREACHED */
				
			} elseif (count($ids)) {
				return
					$this->getListByLogic(
						Expression::in(
							new DBField('id', $this->dao->getTable()),
							$ids
						),
						Cache::DO_NOT_CACHE
					);
			} else
				return array();
		}
		
		public function getListByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			$db = DBPool::getByDao($this->dao);
			
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $list;
			} elseif ($list = $db->queryObjectSet($query, $this->dao)) {
				if (Cache::DO_NOT_CACHE === $expires) {
					return $list;
				} else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else {
				throw new ObjectNotFoundException(
					"zero list for query such query - "
					."'{$query->toDialectString($db->getDialect())}'"
				);
			}
		}
		
		public function getListByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
		{
			return
				$this->getListByQuery(
					$this->dao->makeSelectHead()->andWhere($logic), $expires
				);
		}
		
		public function getPlainList($expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->getListByQuery(
				$this->dao->makeSelectHead(), $expires
			);
		}
		//@}
		
		//@{
		// custom list getters
		public function getCustomList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if ($list = DBPool::getByDao($this->dao)->querySet($query))
				return $list;
			else
				throw new ObjectNotFoundException();
		}
		
		public function getCustomRowList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			if ($list = DBPool::getByDao($this->dao)->queryColumn($query))
				return $list;
			else
				throw new ObjectNotFoundException();
		}
		//@}
		
		//@{
		// query result getters
		public function getCountedList(
			ObjectQuery $oq, $expires = Cache::DO_NOT_CACHE
		)
		{
			return
				$this->getQueryResult(
					$oq->toSelectQuery($this->dao), $expires
				);
		}
		
		public function getQueryResult(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			$db = DBPool::getByDao($this->dao);
			
			$list = $db->queryObjectSet($query, $this->dao);
			
			$count = clone $query;
			
			$count =
				$db->queryRow(
					$count->dropFields()->dropOrder()->limit(null, null)->
					get(SQLFunction::create('COUNT', '*')->setAlias('count'))
				);

			$res = new QueryResult();

			return
				$res->
					setList($list)->
					setCount($count['count'])->
					setQuery($query);
		}
		//@}

		//@{
		// cachers
		public function cacheById(
			Identifiable $object, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			if ($expires !== Cache::DO_NOT_CACHE) {
				
				Cache::me()->mark($this->className)->
					add(
						$this->className.'_'.$object->getId(),
						$object,
						$expires
					);
			}
			
			return $object;
		}
		
		public function cacheByQuery(
			SelectQuery $query,
			/* Identifiable */ $object,
			$expires = Cache::DO_NOT_CACHE
		)
		{
			if ($expires !== Cache::DO_NOT_CACHE) {
			
				Cache::me()->mark($this->className)->
					add(
						$this->className.self::SUFFIX_QUERY.$query->getId(),
						$object,
						$expires
					);
			}
			
			return $object;
		}
		
		public function cacheListByQuery(SelectQuery $query, /* array */ $array)
		{
			throw new UnimplementedFeatureException();
		}
		//@}
		
		//@{
		// erasers
		public function dropById($id)
		{
			$result = parent::dropById($id);
			
			$this->uncacheLists();
			
			return $result;
		}
		//@}
		
		//@{
		// uncachers
		public function uncacheByIds($ids)
		{
			foreach ($ids as $id)
				$this->uncacheById($id);
			
			return $this->uncacheLists();
		}
		
		public function uncacheLists()
		{
			return $this->uncacheByQuery($this->dao->makeSelectHead());
		}
		//@}
	}
?>