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
		protected $selectHead = null;
		
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
			$this->uncacheById($id);
			$this->uncacheList();
			
			return
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->getTable())->
					where(Expression::eq('id', $id))
				);
		}

		public function dropByIds($ids)
		{
			foreach ($ids as $id)
				$this->uncacheById($id);

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
		
		public function uncacheIdentifiable(Identifiable $object)
		{
			$result = $this->uncacheList();

			return $this->uncacheById($object->getId()) && $result;
		}

		/**
		 * operations with single object(s)
		 * cached by default (because you can drop 'em at any time)
		**/
		public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
		{
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($object = $this->getCachedById($id))
			)
				return $object;
			else {
				$db = DBFactory::getDefaultInstance();

				$query = 
					$this->
						makeSelectHead()->
						where(
							Expression::eq(
								DBField::create('id', $this->getTable()),
								$id
							)
						);

				if ($object = $db->queryObjectRow($query, $this)) {
					return
						$expires === Cache::DO_NOT_CACHE
							? $object
							: $this->cacheById($object, $expires);
				} else { 
					throw new ObjectNotFoundException(
						"there is no such object for '".get_class($this)
						."' with query == {$query->toString($db->getDialect())}"
					);
				}
			}
		}
		
		public function getPlainList($expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->getListByQuery($this->makeSelectHead(), $expires);
		}
		
		public function getByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE)
		{
			return
				$this->getByQuery(
					$this->makeSelectHead()->where($logic), $expires
				);
		}

		public function getListByIds($ids, $expires = Cache::EXPIRES_MEDIUM)
		{
			if ($expires !== Cache::DO_NOT_CACHE) {
				$list = array();
				
				if (sizeof($ids) == 1) {
					try {
						return array($this->getById(current($ids)));
					} catch (ObjectNotFoundException $e) {
						return array();
					}
				}
				
				foreach ($ids as $id) {
					try {
						$list[] = $this->getById($id, $expires);
					} catch (ObjectNotFoundException $e) {
						// ignore
					}
				}

				return $list;
			} elseif (sizeof($ids)) {
				return
					$this->getListByLogic(
						Expression::in(
							new DBField('id', $this->getTable()),
							$ids
						),
						Cache::DO_NOT_CACHE
					);
			} else
				return array();
		}

		public function getByQuery(SelectQuery $query, $expires = Cache::EXPIRES_MEDIUM)
		{
			$db = DBFactory::getDefaultInstance();
			
			if (
				($expires !== Cache::DO_NOT_CACHE) &&
				($object = $this->getCachedByQuery($query))
			)
				return $object;
			elseif ($object = $db->queryObjectRow($query, $this)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheById($object, $expires);
			} else
				throw new ObjectNotFoundException(
					"there is no such object for '".get_class($this)
					."' with query == {$query->toString($db->getDialect())}"
				);
		}
		
		/**
		 * list operations and things we can not drop from cache at any time
		**/
		public function getListByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			$db = DBFactory::getDefaultInstance();
			
			if (
				($expires !== Cache::DO_NOT_CACHE) && 
				($list = $this->getCachedByQuery($query))
			)
				return $list;
			elseif ($list = $db->queryObjectSet($query, $this)) {
				if (Cache::DO_NOT_CACHE === $expires) {
					return $list;
				} else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else {
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db->getDialect())}'"
				);
			}
		}

		public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			$db = DBFactory::getDefaultInstance();
			
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);
			
			if (
				($expires !== Cache::DO_NOT_CACHE) &&
				($object = $this->getCachedByQuery($query))
			)
				return $object;
			elseif ($object = $db->queryRow($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheByQuery($query, $object, $expires);
			} else {
				throw new ObjectNotFoundException(
					"zero for query == {$query->toString($db->getDialect())}"
				);
			}
		}

		public function getQueryResult(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			$db = DBFactory::getDefaultInstance();

			if ($result = $this->getCachedQueryResult($query))
				return $result;
			elseif ($result = $db->objectQuery($query, $this)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $result;
				else
					return $this->cacheByQueryResult($result, $expires);
			} else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db->getDialect())}'"
				);
		}
		
		public function getCustomList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			$db = DBFactory::getDefaultInstance();
			
			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			)
				return $list;
			elseif ($list = $db->querySet($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $list;
				else {
					return $this->cacheByQuery($query, $list, $expires);
				}
			} else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db->getDialect())}'"
				);
		}
		
		public function getCustomRowList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			$db = DBFactory::getDefaultInstance();

			if (
				($expires !== Cache::DO_NOT_CACHE)
				&& ($list = $this->getCachedByQuery($query))
			)
				return $list;
			elseif ($list = $db->queryColumn($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $list;
				else
					return $this->cacheByQuery($query, $list, $expires);
			} else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db->getDialect())}'"
				);
		}
		
		public function getListByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE)
		{
			return
				$this->getListByQuery(
					$this->makeSelectHead()->where($logic), $expires
				);
		}
		
		/**
		 * default makeSelectHead's behaviour
		**/
		public function makeSelectHead()
		{
			if (null === $this->selectHead) {
				$this->selectHead = 
					OSQL::select()->
					from($this->getTable());
				
				$table = $this->getTable();
				
				foreach ($this->getFields() as $field)
					$this->selectHead->get(new DBField($field, $table));
			}
			
			return clone $this->selectHead;
		}
	}
?>