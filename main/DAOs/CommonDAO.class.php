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
		
		public function getByLogic(
			LogicalObject $logic, $expires = Cache::DO_NOT_CACHE
		)
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

		public function getQueryResult(SelectQuery $query)
		{
			$db = DBFactory::getDefaultInstance();
			
			$list = $db->queryObjectSet($query, $this);
			
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

		public function getPlainList($expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->getListByQuery($this->makeSelectHead(), $expires);
		}
		
		public function getListByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE)
		{
			return
				$this->getListByQuery(
					$this->makeSelectHead()->where($logic), $expires
				);
		}
	}
?>