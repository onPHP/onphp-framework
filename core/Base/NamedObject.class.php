<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see Named
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	namespace Onphp;

	abstract class NamedObject
		extends IdentifiableObject
		implements Named, Stringable
	{
		protected $name	= null;
		
		public static function compareNames(
			Named $left, Named $right
		)
		{
			return strcasecmp($left->getName(), $right->getName());
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return \Onphp\NamedObject
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function toString()
		{
			return "[{$this->id}] {$this->name}";
		}
	}
?>