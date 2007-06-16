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

	/**
	 * @ingroup Utils
	**/
	abstract class Reader 
	{
		const BLOCK_SIZE = 8192;
		
		abstract public function close();
		abstract public function read($count);
		abstract public function isEof();
		
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
		
		public function available()
		{
			return 0;
		}
		
		public function getWhole()
		{
			while(!$this->isEof())
				$result .= $this->read(self::BLOCK_SIZE);	
			
			return $result;
		}
	}
?>