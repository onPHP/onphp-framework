<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see Named.class.php
	 * 
	 * @ingroup Base
	**/
	abstract class NamedObject extends IdentifiableObject implements Named
	{
		protected $name	= null;
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public static function compareNames(
			NamedObject $left, NamedObject $right
		)
		{
			return strcasecmp($left->name, $right->name);
		}
		
		public function toString()
		{
			return "{$this->id}: {$this->name}";
		}
	}
?>