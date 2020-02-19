<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup DAOs
	**/
	final class CacheSegmentHandler implements SegmentHandler
	{
		private $index = null;
		
		public function __construct($segmentId)
		{
			$this->index = $segmentId;
		}
		
		public function touch($key)
		{
			if (!Cache::me()->append($this->index, $key)) {
				return
					Cache::me()->set(
						$this->index,
						'|'.$key,
						Cache::EXPIRES_FOREVER
					);
			}
			
			return true;
		}
		
		public function unlink($key)
		{
			if ($data = Cache::me()->get($this->index)) {
				return
					Cache::me()->set(
						$this->index,
						str_replace('|'.$key, null, $data),
						Cache::EXPIRES_FOREVER
					);
			}
			
			return false;
		}
		
		public function ping($key)
		{
			if ($data = Cache::me()->get($this->index)) {
				return (strpos($data, '|'.$key) !== false);
			}
			
			return false;
		}
		
		public function drop()
		{
			return Cache::me()->delete($this->index);
		}
	}
?>