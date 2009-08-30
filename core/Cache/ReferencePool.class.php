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
	 * A wrapper to single cache to prevent cloning of returned cached objects.
	 * 
	 * @ingroup Cache
	**/
	final class ReferencePool extends SelectivePeer
	{
		private $peer = null;
		private $pool = array();
		
		public function __construct(CachePeer $peer)
		{
			$this->peer = $peer;
		}
		
		public function mark($className)
		{
			$this->peer->mark($className);
			return $this;
		}
		
		public function get($key)
		{
			if (isset($this->pool[$key]) && $this->pool[$key])
				return $this->pool[$key];
			
			return $this->pool[$key] = $this->peer->get($key);
		}
		
		public function delete($key)
		{
			unset($this->pool[$key]);
			
			return $this->peer->delete($key);
		}
		
		public function clean()
		{
			$this->pool = array();
			
			return $this->peer->clean();
		}
		
		public function isAlive()
		{
			return $this->peer->isAlive();
		}

		public function set($key, &$value, $expires = Cache::EXPIRES_MEDIUM)
		{
			$this->pool[$key] = $value;
			
			return $this->peer->set($key, $value, $expires);
		}
		
		public function add($key, &$value, $expires = Cache::EXPIRES_MEDIUM)
		{
			$this->pool[$key] = $value;
			
			return $this->peer->add($key, $value, $expires);
		}

		public function replace($key, &$value, $expires = Cache::EXPIRES_MEDIUM)
		{
			$this->pool[$key] = $value;
			
			return $this->peer->replace($key, $value, $expires);
		}
		
		protected function store(
			$action, $key, &$value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			throw new UnsupportedMethodException();
		}
	}
?>