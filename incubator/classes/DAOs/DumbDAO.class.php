<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	// non-automatic cache handling here
	abstract class DumbDAO extends SmartDAO
	{
		public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
		{
			$db = DBFactory::getDefaultInstance();
			
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);
			
			if (
				($expires !== Cache::DO_NOT_CACHE) &&
				($object = $this->getCachedQueryResult($query))
			)
				return $object;
			elseif ($object = $db->queryRow($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $object;
				else
					return $this->cacheQueryResult($query, $object, $expires);
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
					return $this->cacheQueryResult($query, $result, $expires);
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
				&& ($list = $this->getCachedQueryResult($query))
			)
				return $list;
			elseif ($list = $db->querySet($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $list;
				else {
					return $this->cacheQueryResult($query, $list, $expires);
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
				&& ($list = $this->getCachedQueryResult($query))
			)
				return $list;
			elseif ($list = $db->queryColumn($query)) {
				if ($expires === Cache::DO_NOT_CACHE)
					return $list;
				else
					return $this->cacheQueryResult($query, $list, $expires);
			} else
				throw new ObjectNotFoundException(
					"zero list for query such query - '{$query->toString($db->getDialect())}'"
				);
		}

		private function cacheQueryResult(SelectQuery $query, $result, $expires)
		{
			$className = $this->getObjectName();
			
			Cache::me()->mark($className)->
				add($className.'_query_'.$query->getId(), $result);
			
			return $this;
		}
		
		private function getCachedQueryResult(SelectQuery $query, $expires)
		{
			$className = $this->getObjectName();
			
			return
				Cache::me()->mark($className)->
					add($className.'_query_'.$query->getId(), $result);
		}
	}
?>