<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class InputStream
	{
		protected $eof	= false;
		
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
			return $this->eof;
		}
		
		public function skip()
		{
			return 0;
		}
		
		public function available()
		{
			return 0;
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