<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
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
	abstract class InputStream
	{
		/**
		 * reads a maximum of $length bytes
		 * 
		 * returns null on eof or if length == 0.
		 * Otherwise MUST return at least one byte
		 * or throw IOException
		 * 
		 * NOTE: if length is too large to read all data at once and eof has
		 * not been reached, it MUST BLOCK until all data is read or eof is
		 * reached or throw IOException.
		 * 
		 * It is abnormal state. Maybe you should use some kind of
		 * non-blocking channels instead?
		 * 
		**/
		abstract public function read($length);
		
		public function isEof()
		{
			return false;
		}
		
		/**
		 * @return InputStream
		**/
		public function mark()
		{
			/* nop */
			
			return $this;
		}
		
		public function markSupported()
		{
			return false;
		}
		
		public function reset()
		{
			throw new IOException(
				'mark has been invalidated'
			);
		}
		
		public function skip($count)
		{
			return strlen($this->read($count));
		}
		
		public function available()
		{
			return 0;
		}
		
		/**
		 * @return InputStream
		**/
		public function close()
		{
			/* nop */
			
			return $this;
		}
	}
?>