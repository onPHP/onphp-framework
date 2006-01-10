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
		private $name		= null;
		private $dumbName	= null;
		private $type		= null;
		private $parent		= null;
		
		private $properties	= array();
		private $interfaces	= array();
		
		private $pattern	= null;
		private $identifier	= null;
		
		private $childs		= false;
		
		public function __construct($name)
		{
			$this->name = $name;
			
			$dumb = strtolower(
				preg_replace(':([A-Z]):', '_\1', $name)
			);
			
			if ($dumb[0] == '_')
				$this->dumbName = substr($dumb, 1);
			else
				$this->dumbName = $dumb;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getDumbName()
		{
			return $this->dumbName;
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
			
			if ($property->isIdentifier())
				$this->identifier = $property;
			
			return $this;
		}
		
		public function dropProperty($name)
		{
			if (isset($this->properties[$name])) {
				
				if ($this->properties[$name]->isIdentifier())
					unset($this->identifier);
				
				unset($this->properties[$name]);
			
			} else
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
		
		public function getIdentifier()
		{
			return $this->identifier;
		}
		
		public function hasChilds()
		{
			return $this->childs;
		}
		
		public function setChilds($exist = false)
		{
			$this->childs = ($exist === true);
			
			return $this;
		}
		
		public function dump()
		{
			return $this->pattern->build($this);
		}
	}
?>