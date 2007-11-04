<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	abstract class Reader
	{
		const BLOCK_SIZE = 16384;
		
		abstract public function close();
		abstract public function read($count);
		
		public function isEof()
		{
			return false;
		}
		
		public function mark()
		{
			throw new IOException('mark() not supported');
		}
		
		public function markSupported()
		{
			return false;
		}
		
		public function reset()
		{
			throw new IOException('reset() not supported');
		}
		
		public function skip($count)
		{
			return mb_strlen($this->read($count));
		}
		
		public function available()
		{
			return 0;
		}
		
		public function getWhole()
		{
			while (!$this->isEof())
				$result .= $this->read(self::BLOCK_SIZE);	
			
			return $result;
		}
	}
?>