<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * SHA-1 based filter: passwords.
	 * 
	 * @ingroup Filters
	**/
	namespace Onphp;

	final class HashFilter implements Filtrator
	{
		private $binary = false;
		
		public function __construct($binary = false)
		{
			$this->binary = ($binary === true);
		}
		
		/**
		 * @return \Onphp\HashFilter
		**/
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