<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see LightMetaProperty
	 * 
	 * @ingroup Helpers
	**/
	final class CompositeLightMetaProperty implements LightPropertyHelper
	{
		private $className = null;
		private $propertyName = null;
		
		private $list = array();
		
		/**
		 * @return CompositeLightMetaProperty
		**/
		public static function create($className, $propertyName)
		{
			return new self($className, $propertyName);
		}
		
		public function __construct($className, $propertyName)
		{
			$this->className = $className;
			$this->propertyName = $propertyName;
		}
		
		public function getGetter()
		{
			return 'get'.ucfirst($this->propertyName);
		}
		
		public function getSetter()
		{
			return 'set'.ucfirst($this->propertyName);
		}
		
		/**
		 * @return CompositeLightMetaProperty
		**/
		public function add(LightMetaProperty $property)
		{
			$name = $property->getName();
			
			Assert::isFalse(isset($this->list[$name]));
			
			$this->list[$name] = $property;
			
			return $this;
		}
		
		/**
		 * @return CompositeLightMetaProperty
		**/
		public function multiAdd()
		{
			foreach (func_get_args() as $property)
				$this->add($property);
			
			return $this;
		}
		
		public function toValue(ProtoDAO $dao, $array, $prefix = null)
		{
			return $dao->getProtoClass()->makeObject(
				$this->className, $array, $prefix
			);
		}
		
		public function processMapping(array $mapping)
		{
			foreach ($this->list as $property) {
				$mapping = $property->processMapping($mapping);
			}
			
			return $mapping;
		}
		
		/**
		 * @return Form
		**/
		public function processForm(Form $form)
		{
			foreach ($this->list as $property)
				$property->processForm($form);
			
			return $form;
		}
		
		/**
		 * @return InsertOrUpdateQuery
		**/
		public function processQuery(
			InsertOrUpdateQuery $query,
			Identifiable $object
		)
		{
			foreach ($this->list as $property)
				$property->processQuery($query, $object);
			
			return $query;
		}
		
		public function has($name)
		{
			return isset($this->list[$name]);
		}
	}
?>