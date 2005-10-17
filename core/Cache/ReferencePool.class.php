<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class ReferencePool extends CachePeer
	{
		private $peer = null;
		private $pool = array();
		
		public function __construct(CachePeer $peer)
		{
			$this->peer = $peer;
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