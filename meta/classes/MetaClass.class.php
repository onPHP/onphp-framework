<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class MetaClass
	{
		private $name	= null;
		private $type	= null;
		private $parent	= null;
		
		private $properties	= array();
		private $interfaces	= array();
		
		private $pattern = null;
		
		public function __construct($name)
		{
			$this->name = $name;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		public function setType(MetaClassType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function getParent()
		{
			return $this->parent;
		}
		
		public function setParent(MetaClass $parent)
		{
			$this->parent = $parent;
			
			return $this;
		}
		
		public function getProperties()
		{
			return $this->properties;
		}
		
		public function addProperty(MetaClassProperty $property)
		{
			$name = $property->getName();
			
			if (!isset($this->properties[$name]))
				$this->properties[$name] = $property;
			else
				throw new DuplicateObjectException(
					"property '{$name}' already exist"
				);
			
			return $this;
		}
		
		public function dropProperty($name)
		{
			if (isset($this->properties[$name]))
				unset($this->properties[$name]);
			else
				throw new ObjectNotFoundException(
					"property '{$name}' does not exist"
				);
			
			return $this;
		}
		
		public function getInterfaces()
		{
			return $this->interfaces;
		}
		
		public function addInterface($name)
		{
			$this->interfaces[] = $name;
			
			return $this;
		}
		
		public function getPattern()
		{
			return $this->pattern;
		}
		
		public function setPattern(BasePattern $pattern)
		{
			$this->pattern = $pattern;
		}
		
		public function dump()
		{
			return $this->pattern->build($this);
		}
	}
?>