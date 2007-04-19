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
		
		final public function getExpandedPropertyList($prefix = null)
		{
			static $lists = array();
			
			$className = get_class($this);
			
			if (!isset($lists[$className])) {
				foreach ($this->makePropertyList() as $property) {
					if ($property instanceof InnerMetaProperty) {
						$lists[$className] =
							array_merge(
								$lists[$className],
								$property->getProto()->getExpandedPropertyList(
									$property->getName().':'
								)
							);
					} else {
						$lists[
							$className
						][
							$prefix.$property->getName()
						]
							= $property;
					}
				}
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
		
		/**
		 * @return Form
		**/
		public function makeForm($prefix = null)
		{
			$form = Form::create();
			
			foreach ($this->getPropertyList() as $property) {
				$property->processForm($form, $prefix);
			}
			
			return $form;
		}
		
		/**
		 * @return InsertOrUpdateQuery
		**/
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
		
		public function importPrimitive(
			$path,
			Form $form,
			BasePrimitive $prm,
			/* Prototyped */ $object,
			$ignoreNull = true
		)
		{
			if (strpos($path, ':') !== false) {
				list($propertyName, $path) = explode(':', $path, 2);
				
				$property = $this->getPropertyByName($propertyName);
				
				Assert::isTrue($property instanceof InnerMetaProperty);
				
				$getter = $property->getGetter();
				
				return $property->getProto()->importPrimitive(
					$path, $form, $prm, $object->$getter(), $ignoreNull
				);
			} else {
				$property = $this->getPropertyByName($path);
				
				if (
					($property->getFetchStrategyId() == FetchStrategy::LAZY)
				)
					return $object;
				
				$getter = $property->getGetter();
				
				$value = $object->$getter();
				
				if (!$ignoreNull || ($value !== null)) {
					$form->importValue($prm->getName(), $value);
				}
			}
			
			return $object;
		}
		
		public function exportPrimitive(
			$path,
			BasePrimitive $prm,
			/* Prototyped */ $object,
			$ignoreNull = true
		) {
			if (strpos($path, ':') !== false) {
				list($propertyName, $path) = explode(':', $path, 2);
				
				$property = $this->getPropertyByName($propertyName);
				
				Assert::isTrue($property instanceof InnerMetaProperty);
				
				$getter = $property->getGetter();
				
				return $property->getProto()->exportPrimitive(
					$path, $prm, $object->$getter(), $ignoreNull
				);
			} else {
				$property = $this->getPropertyByName($path);
				$setter = $property->getSetter();
				$value = $prm->getValue();
				
				if (
					!$ignoreNull || ($value !== null)
				) {
					if ($property->isIdentifier()) {
						$value = $value->getId();
					}
					
					$dropper = $property->getDropper();
					
					if (
						($value === null)
							&& method_exists($object, $dropper)
							&& (
								!$property->getRelationId()
								|| (
									$property->getRelationId()
									== MetaRelation::ONE_TO_ONE
								)
							)
					) {
						$object->$dropper();
						
						return $object;
					} elseif (
						(
							$property->getRelationId()
							== MetaRelation::ONE_TO_MANY
						) || (
							$property->getRelationId()
							== MetaRelation::MANY_TO_MANY
						)
					) {
						if ($value === null)
							$value = array();
						
						$getter = $property->getGetter();
						$object->$getter()->setList($value);
						
						return $object;
					}
					
					$object->$setter($value);
				}
			}
			
			return $object;
		}
	}
?>