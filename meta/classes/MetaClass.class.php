<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup MetaBase
	**/
	class MetaClass
	{
		private $name		= null;
		private $namespace	= null;
		private $tableName	= null;
		private $type		= null;
		
		private $parent		= null;
		
		private $properties	= array();
		private $interfaces	= array();
		private $references	= array();
		
		private $pattern	= null;
		private $identifier	= null;
		
		private $source		= null;
		
		private $strategy	= null;
		
		private $build		= true;
		
		public function __construct($name, $namespace = null)
		{
			$this->name = $name;
			
			$dumb = strtolower(
				preg_replace(':([A-Z]):', '_\1', $name)
			);
			
			if ($dumb[0] == '_')
				$this->tableName = substr($dumb, 1);
			else
				$this->tableName = $dumb;
			
			$namespace = trim($namespace, '\\');
			$this->namespace = '\\'.($namespace ? ($namespace . '\\') : '');
		}
		
		public function getName($prefix = '', $postfix = '')
		{
			return $prefix.$this->name.$postfix;
		}
		
		public function getNamespace()
		{
			return $this->namespace ?: '\\';
		}
		
		public function getFullClassName($prefix = '', $postfix = '')
		{
			return $this->getNamespace().$this->getName($prefix, $postfix);
		}
		
		public function getTableName()
		{
			return $this->tableName;
		}
		
		/**
		 * @return MetaClass
		**/
		public function setTableName($name)
		{
			$this->tableName = $name;
			
			return $this;
		}
		
		/**
		 * @return MetaClassType
		**/
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
		
		/**
		 * @return MetaClass
		**/
		public function setType(MetaClassType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		/**
		 * @return MetaClass
		**/
		public function getParent()
		{
			return $this->parent;
		}
		
		/**
		 * @return MetaClass
		**/
		public function getFinalParent()
		{
			if ($this->parent)
				return $this->parent->getFinalParent();
			
			return $this;
		}
		
		/**
		 * @return MetaClass
		**/
		public function setParent(MetaClass $parent)
		{
			$this->parent = $parent;
			
			return $this;
		}
		
		public function hasBuildableParent()
		{
			return (
				$this->parent
				&& (
					!$this->getParent()->getPattern()
						instanceof InternalClassPattern
				)
			);
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
					$this->parent->getAllProperties(),
					$this->properties
				);
			
			return $this->getProperties();
		}
		
		/// with internal class' properties, if any
		public function getWithInternalProperties()
		{
			if ($this->parent) {
				$out = $this->properties;
				
				$class = $this;
				
				while ($parent = $class->getParent()) {
					if ($parent->getPattern() instanceof InternalClassPattern) {
						$out = array_merge($parent->getProperties(), $out);
					}
					
					$class = $parent;
				}
				
				return $out;
			}
			
			return $this->getProperties();
		}
		
		/// only parents
		public function getAllParentsProperties()
		{
			$out = array();
			
			$class = $this;
			
			while ($parent = $class->getParent()) {
				$out = array_merge($out, $parent->getProperties());
				$class = $parent;
			}
			
			return $out;
		}
		
		/**
		 * @return MetaClass
		**/
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
		
		/**
		 * @return MetaClassProperty
		 * @throws MissingElementException
		**/
		public function getPropertyByName($name)
		{
			if (isset($this->properties[$name]))
				return $this->properties[$name];
			
			throw new MissingElementException("unknown property '{$name}'");
		}
		
		public function hasProperty($name)
		{
			return isset($this->properties[$name]);
		}
		
		/**
		 * @return MetaClass
		**/
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
		
		/**
		 * @return MetaClass
		**/
		public function addInterface($name)
		{
			$this->interfaces[] = $name;
			
			return $this;
		}
		
		/**
		 * @return GenerationPattern
		**/
		public function getPattern()
		{
			return $this->pattern;
		}
		
		/**
		 * @return MetaClass
		**/
		public function setPattern(GenerationPattern $pattern)
		{
			$this->pattern = $pattern;
			
			return $this;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function getIdentifier()
		{
			// return parent's identifier, if we're child
			if (!$this->identifier && $this->parent)
				return $this->parent->getIdentifier();
			
			return $this->identifier;
		}
		
		/**
		 * @return MetaClass
		**/
		public function setSourceLink($link)
		{
			$this->source = $link;
			
			return $this;
		}
		
		public function getSourceLink()
		{
			return $this->source;
		}
		
		/**
		 * @return MetaClass
		**/
		public function setReferencingClass($className)
		{
			$this->references[$className] = true;
			
			return $this;
		}
		
		public function getReferencingClasses()
		{
			return array_keys($this->references);
		}
		
		/**
		 * @return MetaClass
		**/
		public function setFetchStrategy(FetchStrategy $strategy)
		{
			$this->strategy = $strategy;
			
			return $this;
		}
		
		/**
		 * @return FetchStrategy
		**/
		public function getFetchStrategy()
		{
			return $this->strategy;
		}
		
		public function getFetchStrategyId()
		{
			if ($this->strategy)
				return $this->strategy->getId();
			
			return null;
		}
		
		public function hasChilds()
		{
			foreach (MetaConfiguration::me()->getClassList() as $class) {
				if (
					$class->getParent()
					&& $class->getParent()->getName() == $this->getName()
				)
					return true;
			}
			
			return false;
		}
		
		public function dump()
		{
			if ($this->doBuild())
				return $this->pattern->build($this);
			
			return $this->pattern;
		}
		
		public function doBuild()
		{
			return $this->build;
		}
		
		/**
		 * @return MetaClass
		**/
		public function setBuild($do)
		{
			$this->build = $do;
			
			return $this;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function isRedefinedProperty($name)
		{
			$parent = $this;
			
			while ($parent = $parent->getParent()) {
				if ($parent->hasProperty($name))
					return $parent->getPropertyByName($name);
			}
			
			return false;
		}
	}
?>