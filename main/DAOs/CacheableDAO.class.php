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

	/**
	 * Cache related part of CommonDAO.
	 * 
	 * @see Cache
	 * @see CachePeer
	**/
	abstract class CacheableDAO extends GenericDAO
	{
		public function cacheById(Identifiable $object, $expires = null)
		{
			$className = $this->getObjectName();
			
			Cache::me()->mark($className)->
				add($className.'_'.$object->getId(), $object, $expires);
			
			return $object;
		}
		
		public function cacheByQuery(Query $query, &$object, $expires = null)
		{
			$className = $this->getObjectName();
			
			Cache::me()->mark($className)->
				add('query_'.$query->getId(), $object, $expires);
			
			return $object;
		}
		
		public function cacheByQueryResult(QueryResult $result, $expires = null)
		{
			$className = $this->getObjectName();
			
			Cache::me()->mark($className)->
				add('result_'.$result->getQuery()->getId(), $result, $expires);
			
			return $result;
		}

		public function getCachedById($id)
		{
			$className = $this->getObjectName();
			
			return Cache::me()->mark($className)->get($className.'_'.$id);
		}
		
		public function uncacheById($id)
		{
			$className = $this->getObjectName();
			
			return Cache::me()->mark($className)->delete($className.'_'.$id);
		}
		
		public function getCachedByQuery(Query $query)
		{
			return
				Cache::me()->mark($this->getObjectName())->
					get('query_'.$query->getId());
		}
		
		public function uncacheByQuery(Query $query)
		{
			return
				Cache::me()->mark($this->getObjectName())->
					delete('query_'.$query->getId());
		}
		
		public function getCachedQueryResult(SelectQuery $query)
		{
			return
				Cache::me()->mark($this->getObjectName())->
					get('result_'.$query->getId());
		}
		
		public function uncacheByKey($key)
		{
			return Cache::me()->mark($this->getObjectName())->delete($key);
		}
		
		protected function cacheByKey($key, &$object, $expires = null)
		{
			if ($expires === Cache::DO_NOT_CACHE)
				return $object;
			elseif (!$expires)
				$expires = Cache::EXPIRES_MEDIUM;
			
			Cache::me()->mark($this->getObjectName())->
				add($key, $object, $expires);
			
			return $object;
		}

		protected function getCachedByKey($key)
		{
			$something = Cache::me()->mark($this->getObjectName())->get($key);
			
			if ($something || $something === array())
				return $something;
			
			return null;
		}
	}
?>