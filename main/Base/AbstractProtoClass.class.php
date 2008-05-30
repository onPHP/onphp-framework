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
/* $Id$ */

	/**
	 * @ingroup Helpers
	**/
	abstract class AbstractProtoClass extends Singleton
	{
		abstract protected function makePropertyList();
		
		public static function makeOnlyObject($className, $array, $prefix = null)
		{
			return self::assemblyObject(new $className, false, $array, $prefix);
		}
		
		public static function completeObject(
			Prototyped $object, array $array = null, $prefix = null
		)
		{
			if ($array)
				return self::assemblyObject($object, true, $array, $prefix);
			else
				return self::fetchEncapsulants($object);
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
				"unknown property requested by name '{$name}'"
			);
		}
		
		/**
		 * @return Form
		**/
		public function makeForm($prefix = null)
		{
			$form = Form::create();
			
			foreach ($this->getPropertyList() as $property) {
				$property->fillForm($form, $prefix);
			}
			
			return $form;
		}
		
		/**
		 * @return InsertOrUpdateQuery
		**/
		public function fillQuery(
			InsertOrUpdateQuery $query, Prototyped $object
		)
		{
			foreach ($this->getPropertyList() as $property) {
				$property->fillQuery($query, $object);
			}
			
			return $query;
		}
		
		public function getMapping()
		{
			static $mappings = array();
			
			$className = get_class($this);
			
			if (!isset($mappings[$className])) {
				$mapping = array();
				foreach ($this->getPropertyList() as $property) {
					$mapping = $property->fillMapping($mapping);
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
				return $this->forwardPrimitive(
					$path, $form, $prm, $object, $ignoreNull
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
		)
		{
			if (strpos($path, ':') !== false) {
				return $this->forwardPrimitive(
					$path, null, $prm, $object, $ignoreNull
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
		
		private static function fetchEncapsulants(Prototyped $object)
		{
			foreach ($object->proto()->getPropertyList() as $property) {
				if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
					$getter = $property->getGetter();
					
					if (($inner = $object->$getter()) instanceof DAOConnected) {
						$setter = $property->getSetter();
						$object->$setter($inner->dao()->getById($inner->getId()));
					}
				}
			}
			
			return $object;
		}
		
		private static function assemblyObject(
			Prototyped $object, $encapsulants, $array, $prefix = null
		)
		{
			if ($object instanceof DAOConnected)
				$dao = $object->dao();
			else
				$dao = null;
			
			foreach ($object->proto()->getPropertyList() as $property) {
				$setter = $property->getSetter();
				
				if ($property instanceof InnerMetaProperty) {
					$object->$setter(
						$property->toValue($dao, $array, $prefix)
					);
				} elseif ($property->isBuildable($array, $prefix)) {
					if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
						$className = $property->getClassName();
						
						$isEnum = (
							$className
							&& is_subclass_of($className, 'Enumeration')
						);
						
						$columnName = $prefix.$property->getColumnName();
						
						if ($encapsulants) {
							$value =
								$property->toValue(
									$dao,
									array(
										$columnName =>
											$object->
												{$property->getGetter()}()->
													getId()
									),
									$prefix
								);
						} else {
							if (!$isEnum) {
								$value = new $className();
								
								$value->setId(
									$array[$columnName]
								);
							} else {
								$value = $array[$columnName];
							}
						}
						
						if ($isEnum) {
							$value = new $className($value);
						}
						
						$object->$setter($value);
						
						continue;
					}
					
					$object->$setter($property->toValue($dao, $array, $prefix));
				}
			}
			
			return $object;
		}
		
		private function forwardPrimitive(
			$path,
			Form $form = null,
			BasePrimitive $prm,
			/* Prototyped */ $object,
			$ignoreNull = true
		)
		{
			list($propertyName, $path) = explode(':', $path, 2);
			
			$property = $this->getPropertyByName($propertyName);
			
			Assert::isTrue($property instanceof InnerMetaProperty);
			
			$getter = $property->getGetter();
			
			if ($form)
				return $property->getProto()->importPrimitive(
					$path, $form, $prm, $object->$getter(), $ignoreNull
				);
			else
				return $property->getProto()->exportPrimitive(
					$path, $prm, $object->$getter(), $ignoreNull
				);
		}
	}
?>
