<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Filters
	**/
	namespace Onphp;

	final class TrimFilter implements Filtrator
	{
		const LEFT	= 'l';
		const RIGHT	= 'r';
		const BOTH	= null;
		
		private $charlist	= null;
		private $direction	= self::BOTH;
		
		/**
		 * @return \Onphp\TrimFilter
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\TrimFilter
		**/
		public function setLeft()
		{
			$this->direction = self::LEFT;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\TrimFilter
		**/
		public function setRight()
		{
			$this->direction = self::RIGHT;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\TrimFilter
		**/
		public function setBoth()
		{
			$this->direction = self::BOTH;
			
			return $this;
		}
		
		public function apply($value)
		{
			$function = $this->direction.'trim';
			
			return (
				$this->charlist
					? $function($value, $this->charlist)
					: $function($value)
				);
		}
		
		/**
		 * @return \Onphp\TrimFilter
		**/
		public function setCharlist($charlist)
		{
			$this->charlist = $charlist;
			
			return $this;
		}
	}
?>