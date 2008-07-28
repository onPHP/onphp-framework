<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartLegendPositionType extends Enumeration
		implements Stringable
	{
		const BOTTOM 	= 0x1;
		const TOP		= 0x2;
		const LEFT		= 0x3;
		const RIGHT		= 0x4;
		
		protected $names = array(
			self::BOTTOM 	=> 'b',
			self::TOP 		=> 't',
			self::LEFT 		=> 'l',
			self::RIGHT 	=> 'r'
		);
		
		private $paramName = 'chdlp';
		
		/**
		 * @return GoogleChartLegendPositionType
		**/
		public static function create($id)
		{
			return new self($id);
		}
		
		public function toString()
		{
			return $this->paramName.'='.$this->name;
		}
	}
?>