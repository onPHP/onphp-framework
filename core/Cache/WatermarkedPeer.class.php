<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Watermark's all cache activity to avoid namespace collisions.
	 * 
	 * @ingroup Cache
	**/
	final class WatermarkedPeer extends SelectivePeer
	{
		private $peer		= null;
		private $watermark	= null;
		
		public function __construct(
			CachePeer $peer,
			$watermark = "Single onPHP's project"
		)
		{
			$this->peer = $peer;
			$this->watermark = md5($watermark.' ['.ONPHP_VERSION.']');
		}
		
		public function getWatermark()
		{
			return $this->watermark;
		}
		
		public function mark($className)
		{
			$this->peer->mark($this->watermark.$className);
			return $this;
		}
		
		public function get($key)
		{
			return $this->peer->get($this->watermark.$key);
		}
		
		public function delete($key)
		{
			return $this->peer->delete($this->watermark.$key);
		}
		
		public function clean()
		{
			return $this->peer->clean();
		}
		
		public function isAlive()
		{
			return $this->peer->isAlive();
		}
		
		protected function store(
			$action, $key, &$value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			return $this->peer->$action($this->watermark.$key, $value, $expires);
		}
	}
?>