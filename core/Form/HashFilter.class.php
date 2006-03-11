<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
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
	 * 
	 * @ingroup Filters
	**/
	class HashFilter implements Filtrator 
	{
		private $binary = false;
		
		public function __construct($binary = false)
		{
			$this->binary = ($binary === true);
		}
		
		public static function create($binary = false)
		{
			return new self($binary);
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