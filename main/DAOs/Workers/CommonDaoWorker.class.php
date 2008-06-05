<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Tunable (aka manual) caching DAO worker.
	 * 
	 * @see SmartDaoWorker for auto-caching one.
	 * 
	 * @ingroup DAOs
	**/
	class CommonDaoWorker extends BaseDaoWorker
	{
		/// single object getters
		//@{
		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($object = $this->getCachedById($id))
			) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $this->dao->completeObject($object);
			} else {
				$query =
					$this->dao->
						makeSelectHead()->
						andWhere(
							Expression::eq(
								DBField::create(
									$this->dao->getIdName(),
									$this->dao->getTable()
								),
								$id
							)
						);
				
				if ($expires === Cache::DO_NOT_CACHE) {
					$object = $this->fetchObject($query);
				} else {
					$object = $this->cachedFetchObject($query, $expires, true);
				}
				
				if ($object) {
					return $object;
				} else {
					throw new ObjectNotFoundException(
						"there is no such object for '".$this->dao->getObjectName()
						.(
							defined('__LOCAL_DEBUG__')
								?
									"' with query == "
									.$query->toDialectString(
										DBPool::me()->getByDao($this->dao)->
											getDialect()
									)
								: null
						)
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
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($object = $this->getCachedByQuery($query))
			) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $this->dao->completeObject($object);
			} else {
				if ($expires === Cache::DO_NOT_CACHE)
					$object = $this->fetchObject($query);
				else
					$object = $this->cachedFetchObject($query, $expires, false);
				
				if ($object)
					return $object;
				else
					throw new ObjectNotFoundException(
						"there is no such object for '".$this->dao->getObjectName()
							.(
								defined('__LOCAL_DEBUG__')
									?
										"' with query == "
										.$query->toDialectString(
											DBPool::me()->getByDao($this->dao)->
												getDialect()
										)
									: null
							)
					);
			}
		}
		
		public function getCustom(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);
			
			$db = DBPool::getByDao($this->dao);
			
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
					"zero"
					.(
						defined('__LOCAL_DEBUG__')
							?
								"for query == "
								.$query->toDialectString(
									DBPool::me()->getByDao($this->dao)->
										getDialect()
								)
							: null
					)
				);
			}
		}
		//@}

		/// object's list getters
		//@{
		public function getListByIds(
			array $ids,
			$expires = Cache::EXPIRES_MEDIUM
		)
		{
			if ($expires !== Cache::DO_NOT_CACHE) {
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
								$list[] = $this->dao->completeObject($cached);
							else
								$toFetch[] = $ids[$key];
						}
					}
				} else {
					$toFetch = $ids;
				}
				
				if ($toFetch) {
					try {
						$list =
							$list
							+ $this->getListByLogic(
								Expression::in(
									new DBField(
										$this->dao->getIdName(),
										$this->dao->getTable()
									),
									$toFetch
								),
								Cache::DO_NOT_CACHE
							);
					} catch (ObjectNotFoundException $e) {
						// nothing to fetch
					}
				}
			} elseif (count($ids)) {
				try {
					$list =
						$this->getListByLogic(
							Expression::in(
								new DBField(
									$this->dao->getIdName(),
									$this->dao->getTable()
								),
								$ids
							),
							Cache::DO_NOT_CACHE
						);
				} catch (ObjectNotFoundException $e) {/*_*/}
			}
			
			return $list;
		}
		
		public function getListByQuery(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $list;
			} elseif ($list = $this->fetchList($query)) {
				if (Cache::DO_NOT_CACHE === $expires) {
					return $list;
				} else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else {
				throw new ObjectNotFoundException(
					"empty list"
					.(
						defined('__LOCAL_DEBUG__')
							?
								" for such query - "
								.$query->toDialectString(
									DBPool::me()->getByDao($this->dao)->
										getDialect()
								)
							: null
					)
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
		
		public function getPlainList($expires = Cache::DO_NOT_CACHE)
		{
			return $this->getListByQuery(
				$this->dao->makeSelectHead(), $expires
			);
		}
		//@}
		
		/// custom list getters
		//@{
		public function getCustomList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $list;
			} elseif ($list = DBPool::getByDao($this->dao)->querySet($query)) {
				if (Cache::DO_NOT_CACHE === $expires) {
					return $list;
				} else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else {
				throw new ObjectNotFoundException(
					"empty list"
					.(
						defined('__LOCAL_DEBUG__')
							?
								" for such query - "
								.$query->toDialectString(
									DBPool::me()->getByDao($this->dao)->
										getDialect()
								)
							: null
					)
				);
			}
		}
		
		public function getCustomRowList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				
				return $list;
			} elseif ($list = DBPool::getByDao($this->dao)->queryColumn($query)) {
				if (Cache::DO_NOT_CACHE === $expires) {
					return $list;
				} else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else {
				throw new ObjectNotFoundException(
					"empty list"
					.(
						defined('__LOCAL_DEBUG__')
							?
								" for such query - "
								.$query->toDialectString(
									DBPool::me()->getByDao($this->dao)->
										getDialect()
								)
							: null
					)
				);
			}
		}
		//@}
		
		/// query result getters
		//@{
		public function getQueryResult(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			) {
				return $list;
			} else {
				$list = $this->fetchList($query);
				
				$count = clone $query;
				
				$count =
					DBPool::getByDao($this->dao)->queryRow(
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
								QueryResult::create(),
						
						$expires
					);
			}
		}
		//@}

		/// cachers
		//@{
		protected function cacheById(
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
		
		protected function cacheByQuery(
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
		
		protected function cacheListByQuery(
			SelectQuery $query,
			/* array || Cache::NOT_FOUND */ $array
		)
		{
			throw new UnimplementedFeatureException();
		}
		//@}
		
		/// erasers
		//@{
		public function dropById($id)
		{
			$result = parent::dropById($id);
			
			$this->dao->uncacheLists();
			
			return $result;
		}
		//@}

		/// uncachers
		//@{
		public function uncacheByIds($ids)
		{
			foreach ($ids as $id)
				$this->uncacheById($id);
			
			return $this->dao->uncacheLists();
		}
		
		// quite useless here
		public function uncacheLists()
		{
			return $this->uncacheByQuery($this->dao->makeSelectHead());
		}
		//@}
	}
?>