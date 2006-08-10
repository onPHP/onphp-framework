<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	final class eAcceleratorSegmentHandler implements SegmentHandler
	{
		private $id		= null;
		private $locker	= null;
		
		public function __construct($segmentId)
		{
			$this->id = $segmentId;
			$this->locker = Singleton::getInstance('eAcceleratorLocker');
		}
		
		public function touch($key)
		{
			$map = $this->getMap();
			
			if (!isset($map[$key])) {
				$map[$key] = true;
				return $this->storeMap($map);
			}
			
			$this->locker->free($this->id);
			return true;
		}
		
		public function unlink($key)
		{
			$map = $this->getMap();
			
			if (isset($map[$key])) {
				unset($map[$key]);
				return $this->storeMap($map);
			}
			
			$this->locker->free($this->id);
			return true;
		}
		
		public function ping($key)
		{
			$map = $this->getMap();
			
			$this->locker->free($this->id);
			
			if (isset($map[$key])) {
				return true;
			} else {
				return false;
			}
		}
		
		public function drop()
		{
			return eaccelerator_rm($this->id);
		}
		
		private function getMap()
		{
			$this->locker->get($this->id);
			
			if (!$map = eaccelerator_get($this->id)) {
				$map = array();
			}
			
			return $map;
		}
		
		private function storeMap(array $map)
		{
			$result = eaccelerator_put($this->id, $map, Cache::EXPIRES_FOREVER);
			
			$this->locker->free($this->id);
			
			return $result;
		}
	}
?>