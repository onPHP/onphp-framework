<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Anton Lebedevich   *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class CommonDAO extends CacheableDAO
	{
		/**
		 * quite common and must-have methods
		**/
		abstract protected function makeObject(&$array, $prefix = null);
		abstract public function getTable();
		
		public function getFields()
		{
			return $this->fields;
		}
		
		public function getSequence()
		{
			return $this->getTable().'_id';
		}

		/**
		 * erasers area
		**/
		public function dropById($id)
		{
			$this->uncacheByKey($this->getObjectName().'_'.$id);
			$this->uncacheList();
			
			return
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::eq('id', $id))
				);
		}

		public function dropByIds($ids)
		{
			if (Memcached::isFunctional()) {
				foreach ($ids as $ids)
					$this->uncacheByKey($this->getObjectName().'_'.$id);
			}

			return
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::in('id', $ids))
				);
		}
		
		public function uncacheList()
		{
			return $this->uncacheByQuery($this->makeSelectHead());
		}

		/**
		 * operations with single object(s)
		 * cached by default (because you can drop 'em at any time)
		**/
		public function getById($id, $expires = Memcached::EXPIRES_MEDIUM)
		{
			if (
				($expires !== Memcached::DO_NOT_CACHE) &&
				($object = $this->getCachedById($this->getObjectName(), $id))
			)
				return $object;
			elseif ($expires === Memcached::DO_NOT_CACHE)
				return
					$this->getByLogic(
						Expression::eq(
							new DBField('id', $this->getTable()),
							$id
						),
						$expires
					);
			else
				return 
					$this->cacheById(
						$this->getByLogic(
							Expression::eq(
								new DBField('id', $this->getTable()),
								$id
							),
							$expires
						),
						$expires
					);
		}
		
		public function getPlainList($expires = Memcached::EXPIRES_MEDIUM)
		{
			return $this->getListByQuery($this->makeSelectHead(), $expires);
		}
		
		public function getByLogic(LogicalObject $logic, $expires = Memcached::DO_NOT_CACHE)
		{
			return $this->getByQuery($this->makeSelectHead()->where($logic), $expires);
		}

		public function getListByIds(&$ids, $expires = Memcached::EXPIRES_MEDIUM)
		{
			if ($expires !== Memcached::DO_NOT_CACHE && 
				Memcached::getInstance()->isFunctional()
			) {
				$list = array();
				$toFetch = array();
				
				foreach ($ids as $id) {
					$object = $this->getCachedById($this->getObjectName(), $id);
					
					if (is_object($object))
						$list[] = $object;
					else {
						try {
							$list[] = $this->getById($id, $expires);
						} catch (ObjectNotFoundException $e) {
							// TODO: to drop or ! 2drop
						}
					}
				}
				
				if ($size = sizeof($toFetch)) {
					if ($size == 1) {
						try {
							return array($this->getById($toFetch[0]));
						} catch (ObjectNotFoundException $e) {
							return array();
						}
					} else {
						$fetchedList = 
							$this->getListByLogic(
								Expression::in(
									new DBField('id', $this->getTable()),
									$toFetch
								)
							);
					}
						
					for ($i = 0; $i < sizeof($fetchedList); $i++)
						$this->cacheById($fetchedList[$i], $expires);

					$list = array_merge($list, $fetchedList);
				}

				return $list;
			} elseif (sizeof($ids)) {
				return
					$this->getListByLogic(
						Expression::in(
							new DBField('id', $this->getTable()),
							$ids
						),
						Memcached::DO_NOT_CACHE
					);
			} else
				return array();
		}

		public function getByQuery(SelectQuery $query, $expires = Memcached::EXPIRES_MEDIUM)
		{
			$db = &DBFactory::getDefaultInstance();

			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);

			if (
				($expires !== Memcached::DO_NOT_CACHE) &&
				($object = $this->getCachedByQuery($query))
			)
				return $object;
			elseif ($object = $db->queryObjectRow($query, $this)) {
				if ($expires === Memcached::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheById($object, $expires);
			}
			else 
				throw new ObjectNotFoundException(
					"there is no such object for '{$this->getClass()}' with query == {$query->toString($db)}"
				);
		}
		
		/**
		 * list operations and things we can not drop from cache at any time
		**/
		public function getListByQuery(SelectQuery $query, $expires = Memcached::DO_NOT_CACHE)
		{
			$db = &DBFactory::getDefaultInstance();
			
			if (!Memcached::getInstance()->isFunctional())
				$expires = Memcached::DO_NOT_CACHE;

			if (
				($expires !== Memcached::DO_NOT_CACHE) && 
				($list = $this->getCachedByQuery($query))
			) {
				return $list;
			} elseif ($list = $db->queryObjectSet($query, $this)) {
				if (Memcached::DO_NOT_CACHE === $expires) {
					return $list;
				} else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else {
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db)}'"
				);
			}
		}

		public function getCustom(SelectQuery $query, $expires = Memcached::DO_NOT_CACHE)
		{
			$db = &DBFactory::getDefaultInstance();

			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);
			
			if (
				($expires !== Memcached::DO_NOT_CACHE) &&
				($object = $this->getCachedByQuery($query))
			)
				return $object;
			elseif ($object = $db->queryRow($query)) {
				if ($expires === Memcached::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheByQuery($query, $object, $expires);
			}
			else 
				throw new ObjectNotFoundException(
					"zero for query == {$query->toString($db)}"
				);
		}

		public function getQueryResult(SelectQuery $query, $expires = Memcached::DO_NOT_CACHE)
		{
			$db = &DBFactory::getDefaultInstance();

			if ($result = $this->getCachedQueryResult($query))
				return $result;
			elseif ($result = $db->objectQuery($query, $this)) {
				if ($expires === Memcached::DO_NOT_CACHE)
					return $result;
				else
					return $this->cacheByQueryResult($result, $expires);
			}
			else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db)}'"
				);
		}
		
		public function getCustomList(SelectQuery $query, $expires = Memcached::DO_NOT_CACHE)
		{
			$db = &DBFactory::getDefaultInstance();
			
			if (
				($expires !== Memcached::DO_NOT_CACHE) &&
				($list = $this->getCachedByQuery($query))
			)
				return $list;
			elseif ($list = $db->querySet($query)) {
				if ($expires === Memcached::DO_NOT_CACHE)
					return $list;
				else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db)}'"
				);
		}
		
		public function getCustomRowList(SelectQuery $query, $expires = Memcached::DO_NOT_CACHE)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			$db = &DBFactory::getDefaultInstance();

			if ($expires !== Memcached::DO_NOT_CACHE &&
					$list = $this->getCachedByQuery($query)
			)
				return $list;
			elseif ($list = $db->queryColumn($query)) {
				if ($expires === Memcached::DO_NOT_CACHE)
					return $list;
				else
					return $this->cacheByQuery($query, $list, $expires);
			}
			else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db)}'"
				);
		}
		
		public function getListByLogic(LogicalObject $logic, $expires = Memcached::DO_NOT_CACHE)
		{
			return $this->getListByQuery($this->makeSelectHead()->where($logic), $expires);
		}
		
		/**
		 * default makeSelectHead's behaviour
		**/
		protected function makeSelectHead()
		{
			return
				OSQL::select()->
					from($this->getTable())->
					arrayGet($this->getFields());
		}
	}
?>
