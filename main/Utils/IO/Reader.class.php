<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry Lomash                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class Reader 
	{
		protected $eof	= false;
		
		abstract public function close();
		
		abstract public function read($count);
		
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
			if ($count < 0)
				throw new WrongArgumentException('skip value is negative');
			
			return mb_strlen($this->read($count));
		} 
		
		// TODO: may be useless method. Think about it.		
		public function isEof()
		{
			return $this->eof;
		}
	}
?>