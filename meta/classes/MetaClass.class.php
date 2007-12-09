<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup MetaBase
	**/
	class MetaClass
	{
		private $name		= null;
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
		
		public function __construct($name)
		{
			$this->name = $name;
			
			$dumb = strtolower(
				preg_replace(':([A-Z]):', '_\1', $name)
			);
			
			if ($dumb[0] == '_')
				$this->tableName = substr($dumb, 1);
			else
				$this->tableName = $dumb;
		}
		
		public function getName()
		{
			return $this->name;
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
					!$this->getFinalParent()->getPattern()
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
			if (
				$this->parent
				&& (
					$this->getFinalParent()->getPattern()
						instanceof InternalClassPattern
				)
			) {
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
		public function getParentsProperties()
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
		
		public function toComplexType(&$containers, $withoutSoap)
		{
			$abstractType =
				($this->pattern instanceof AbstractClassPattern)
					? " abstract=\"true\" "
					: null;
			
			$element =
<<<XML

	<complexType name="{$this->getName()}"
					{$abstractType}
	>

XML;
			
			if ($this->getParent()) {
				$element .=
<<<XML

		<complexContent>
			<extension base="tns:{$this->getParent()->getName()}">

XML;
			}
			
			$element .=
<<<XML
		<sequence>
XML;
			
			foreach ($this->properties as $property) {
				$generateRestriction = false;
			
				$element .=
<<<XML

			<element
				name="{$property->getName()}"
				
XML;
					$xsdType = null;
					
					if ($property->getType() instanceof ObjectType) {
						if (
							$property->getRelation()
							&&
								$property->getRelation()->getId()
								== MetaRelation::ONE_TO_MANY
						) {
							$containerName =
								self::makeXsdContainerName(
									$property->getType()
								);
							
							$containers[$containerName] = $property->getType();
							
							$xsdType = "tns:" . $containerName;
						} else
							$xsdType = $property->getType()->toXsdType();
					} else
						$xsdType = $property->getType()->toXsdType();
				
				if ($property->getSize()) {
					if (!$withoutSoap) {
						if ($property->getType() instanceof FixedLengthStringType)
							$element .= " minLength=\"" . $property->getSize() . "\" ";
						
						$element .= " maxLength=\"" . $property->getSize() . "\" ";
					} else
						$generateRestriction = true;
				}
				
				if ($generateRestriction) {
					$element .= <<<XML
							>
				<simpleType>
					<xsd:restriction base="{$xsdType}">
						<xsd:maxLength value="{$property->getSize()}"/>
					</xsd:restriction>
				</simpleType>
XML;
				} else {
					$element .=
<<<XML
				type="{$xsdType}"
XML;
				}

				
				if ($generateRestriction) {
					$element .= <<<XML
					
			</element>
XML;
				} else {
					$element .= <<<XML
							/>
XML;
				}
			}
			
			$element .=
<<<XML

		</sequence>
XML;
			
			if ($this->getParent()) {
				$element .=
<<<XML

			</extension>
		</complexContent>
XML;
			}
			
			$element .=
<<<XML

	</complexType>

XML;
			
			return $element;
		}
		
		public static function buildXsdContainer(
			ObjectType $object, $withoutSoap = false
		)
		{
			$typeName = self::makeXsdContainerName($object);
			
			if (!$withoutSoap) {
				$containerXml =
<<<XML
	<complexType name="{$typeName}">
		<complexContent>
			<restriction base="soapenc:Array">
				<attribute
					ref="soapenc:arrayType"
					wsdl:arrayType="tns:{$object->getClass()->getName()}[]"
				/>
			</restriction>
		</complexContent>
	</complexType>

XML;
			} else {
				$className = mb_strtolower($object->getClass()->getName());
				
				$containerXml =
<<<XML
	<complexType name="{$typeName}">
		<sequence>
			<element
				name="{$className}"
				minOccurs="0"
				maxOccurs="unbounded"
				type="tns:{$object->getClass()->getName()}"
			/>
		</sequence>
	</complexType>

XML;
				}
				
				return $containerXml;
		}
		
		private static function makeXsdContainerName(ObjectType $object)
		{
			return $object->getClass()->getName() . 'List';
		}
	}
?>