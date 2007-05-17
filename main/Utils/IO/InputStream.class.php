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
		 * returns false on eof
		 * if length > 0, MUST return at least one byte
		 * or throw an IOException
		**/
		abstract public function read($length);
		
		public function isEof()
		{
			return $this->eof;
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