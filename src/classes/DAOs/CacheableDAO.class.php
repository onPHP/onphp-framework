<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class CacheableDAO extends Singletone
	{
		abstract public function getObjectName();
		
		public function cacheById(Identifiable $object, $expires = null)
		{
			return $this->cacheByKey($this->getObjectName().'_'.$object->getId(), $object, $expires);
		}
		
		public function cacheByQuery(Query $query, &$object, $expires = null)
		{
			return $this->cacheByKey('query_'.$query->getHash(), $object, $expires);
		}
		
		public function cacheByQueryResult(QueryResult $result, $expires = null)
		{
			return $this->cacheByKey('result_'.$result->getQuery()->getHash(), $result, $expires);
		}

		public function getCachedById($className, $id)
		{
			return $this->getCachedByKey($className.'_'.$id);
		}
		
		public function uncacheById(Identifiable $object)
		{
			return $this->uncacheByKey($this->getObjectName().'_'.$object->getId());
		}
		
		public function getCachedByQuery(Query $query)
		{
			return $this->getCachedByKey('query_'.$query->getHash());
		}
		
		public function uncacheByQuery(Query $query)
		{
			return $this->uncacheByKey('query_'.$query->getHash());
		}
		
		public function getCachedQueryResult(SelectQuery $query)
		{
			return $this->getCachedByKey('result_'.$query->getHash());
		}
		
		public function getClass()
		{
			return get_class($this);
		}

		public function uncacheByKey($key)
		{
			$mc = &Memcached::getInstance();
			
			if ($mc->isFunctional())
				return $mc->delete($key);

			return false;
		}
		
		protected function cacheByKey($key, &$object, $expires = null)
		{
			$mc = &Memcached::getInstance();

			if ($expires === Memcached::DO_NOT_CACHE)
				return $object;
			elseif (!$expires)
				$expires = Memcached::EXPIRES_MEDIUM;
				
			if ($mc->isFunctional())
				$mc->add($key, $object, $expires);

			return $object;
		}

		protected function getCachedByKey($key)
		{
			$mc = Memcached::getInstance();
			
			if ($mc->isFunctional()) {
				$something = $mc->get($key);
				
				if ($something || $something === array())
					return $something;
			}

			return null;
		}
	}
?>