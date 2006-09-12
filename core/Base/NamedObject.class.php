<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see Named
	 * @see NamedObjectDAO
	 * @see FinalNamedObjectDAO
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	abstract class NamedObject
		extends IdentifiableObject
		implements Named, Stringable
	{
		protected $name	= null;
		
		public static function compareNames(
			NamedObject $left, NamedObject $right
		)
		{
			return strcasecmp($left->getName(), $right->getName());
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function toString()
		{
			return "{$this->id}: {$this->name}";
		}
	}
?>