<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Default process RAM cache.
	 * 
	 * @see ReferencePool
	 * 
	 * @ingroup Cache
	**/
	final class RuntimeMemory extends CachePeer
	{
		private $cache = array();
		
		public static function create()
		{
			return new self;
		}
		
		public function isAlive()
		{
			return true;
		}
		
		public function get($key)
		{
			if (isset($this->cache[$key]))
				return $this->cache[$key];
			
			return null;
		}
		
		public function delete($key)
		{
			if (isset($this->cache[$key])) {
				unset($this->cache[$key]);
				return true;
			}
			
			return false;
		}
		
		public function clean()
		{
			$this->cache = array();
			
			return $this;
		}

		protected function store($action, $key, &$value, $expires = 0)
		{
			if ($action == 'add' && isset($this->cache[$key]))
				return true;
			elseif ($action == 'replace' && !isset($this->cache[$key]))
				return false;
			
			$this->cache[$key] = $value;
			return true;
		}
	}
?>