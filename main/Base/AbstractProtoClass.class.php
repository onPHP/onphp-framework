<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Helpers
	**/
	abstract class AbstractProtoClass extends Singleton
	{
		abstract protected function makePropertyList();
		
		public static function makeObject($className, $array, $prefix = null)
		{
			$object = new $className;
			
			if ($object instanceof DAOConnected)
				$dao = $object->dao();
			else
				$dao = null;
			
			foreach ($object->proto()->getPropertyList() as $property) {
				if ($property->isBuildable($array, $prefix)) {
					$setter = $property->getSetter();
					$object->$setter($property->toValue($dao, $array, $prefix));
				}
			}
			
			return $object;
		}
		
		final public function getPropertyList()
		{
			static $lists = array();
			
			$className = get_class($this);
			
			if (!isset($lists[$className])) {
				$lists[$className] = $this->makePropertyList();
			}
			
			return $lists[$className];
		}
		
		/**
		 * @return LightMetaProperty
		 * @throws MissingElementException
		**/
		public function getPropertyByName($name)
		{
			$list = $this->getPropertyList();
			
			if (isset($list[$name]))
				return $list[$name];
			
			throw new MissingElementException(
				'unknown property requested by name '."'{$name}'"
			);
		}
		
		public function makeForm()
		{
			$form = Form::create();
			
			foreach ($this->getPropertyList() as $property) {
				$property->processForm($form);
			}
			
			return $form;
		}
		
		public function processQuery(
			InsertOrUpdateQuery $query, Prototyped $object
		) {
			foreach ($this->getPropertyList() as $property) {
				$property->processQuery($query, $object);
			}
			
			return $query;
		}
		
		public function getMapping()
		{
			static $mappings = array();
			
			$className = get_class($this);
			
			if (!isset($mappings[$className])) {
				$mapping = array();
				foreach ($this->getPropertyList() as $name => $property) {
					$mapping = $property->processMapping($mapping);
				}
				$mappings[$className] = $mapping;
			}
			
			return $mappings[$className];
		}
	}
?>