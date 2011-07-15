<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
	
	/**
	 * Basis for almost all implementations of SQL parts.
	 * 
	 * @ingroup OSQL
	**/
	final class UnquotedDbString implements DialectString
	{
		private $string = null;
		
		public static function create($string)
		{
			return new self($string);
		}
		
		public function __construct($string)
		{
			$this->string = $string;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			//lol
			return $this->string;
		}
	}

?>
