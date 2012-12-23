<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see http://eaccelerator.net/
	 * 
	 * @ingroup DAOs
	**/
	final class eAcceleratorSegmentHandler extends OptimizerSegmentHandler
	{
		public function __construct($segmentId)
		{
			parent::__construct($segmentId);
			
			$this->locker = Singleton::getInstance('eAcceleratorLocker');
		}
		
		public function drop()
		{
			return eaccelerator_rm($this->id);
		}
		
		protected function getMap()
		{
			$this->locker->get($this->id);
			
			if (!$map = eaccelerator_get($this->id)) {
				$map = array();
			}
			
			return $map;
		}
		
		protected function storeMap(array $map)
		{
			$result = eaccelerator_put($this->id, $map, Cache::EXPIRES_FOREVER);
			
			$this->locker->free($this->id);
			
			return $result;
		}
	}
