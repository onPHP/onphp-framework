<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * SHA-1 based filter: passwords.
	**/
	class HashFilter implements Filtrator 
	{
		private $binary = false;
		
		public function create($binary = false)
		{
			$hashFilter =  new self;
			
			if ($binary)
				$hashFilter->binary();
			
			return $hashFilter;
		}
		
		public function binary()
		{
			$this->binary = true;
			
			return $this;
		}
		
		public function notBinary()
		{
			$this->binary = false;
			
			return $this;
		}
		
		public function isBinary()
		{
			return $this->binary;
		}
		
		public function apply($value)
		{
			return sha1($value, $this->binary);
		}
	}
?>
