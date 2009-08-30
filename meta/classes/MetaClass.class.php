<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup MetaBase
	**/
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
		
		private $source		= null;
		
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
		
		public function getTypeId()
		{
			return
				$this->type
					? $this->type->getId()
					: null;
		}
		
		public function setType(MetaClassType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function getFinalParent()
		{
			if ($this->parent)
				return $this->parent->getFinalParent();
			
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
		
		/// with parent ones
		public function getAllProperties()
		{
			if ($this->parent)
				return array_merge(
					$this->parent->getProperties(),
					$this->properties
				);
			
			return $this->getProperties();
		}
		
		public function addProperty(MetaClassProperty $property)
		{
			$name = $property->getName();
			
			if (!isset($this->properties[$name]))
				$this->properties[$name] = $property;
			else
				throw new WrongArgumentException(
					"property '{$name}' already exist"
				);
			
			if ($property->isIdentifier())
				$this->identifier = $property;
			
			return $this;
		}
		
		public function hasProperty($name)
		{
			return isset($this->properties[$name]);
		}
		
		public function dropProperty($name)
		{
			if (isset($this->properties[$name])) {
				
				if ($this->properties[$name]->isIdentifier())
					unset($this->identifier);
				
				unset($this->properties[$name]);
			
			} else
				throw new MissingElementException(
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
		
		public function setPattern(GenerationPattern $pattern)
		{
			$this->pattern = $pattern;
		}
		
		public function getIdentifier()
		{
			// return parent's identifier, if we're child
			if (!$this->identifier && $this->parent)
				return $this->parent->getIdentifier();
			
			return $this->identifier;
		}
		
		public function setSourceLink($link)
		{
			$this->source = $link;
			
			return $this;
		}
		
		public function getSourceLink()
		{
			return $this->source;
		}
		
		public function dump()
		{
			return $this->pattern->build($this);
		}
	}
?>