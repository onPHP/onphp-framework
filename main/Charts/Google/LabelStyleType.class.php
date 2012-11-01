<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	// TODO: support for currency
	
	/**
	 * @ingroup GoogleChart
	**/
	namespace Onphp;

	final class LabelStyleType extends Enumeration
	{
		const FLOAT				= 0x1;
		const PERCENT			= 0x2;
		const SCIENTIFIC		= 0x3;
		
		protected $names = array(
			self::FLOAT			=> 'f',
			self::PERCENT		=> 'p',
			self::SCIENTIFIC	=> 'e'
		);
		
		/**
		 * @return \Onphp\LabelStyleType
		**/
		public static function create($id)
		{
			return new self($id);
		}
		
		public function toString()
		{
			return $this->name;
		}
	}
?>