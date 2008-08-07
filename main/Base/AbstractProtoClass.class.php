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
		private $depth = 0;
		private $storage = array();
		private $skipList = array();
		
		abstract protected function makePropertyList();
		
		/**
		 * @return AbstractProtoClass
		**/
		public function beginPrefetch()
		{
			$this->storage[++$this->depth] = array();
			$this->skipList[$this->depth] = array();
			
			return $this;
		}
		
		/**
		 * @return AbstractProtoClass
		**/
		public function skipObjectPrefetching(Identifiable $object)
		{
			if ($this->depth) {
				if (!isset($this->skipList[$this->depth][$object->getId()]))
					$this->skipList[$this->depth][$object->getId()] = 1;
				else
					++$this->skipList[$this->depth][$object->getId()];
			}
			
			return $this;
		}
		
		public function endPrefetch(array $objectList)
		{
			if (!$this->depth)
				throw new WrongStateException('prefetch mode is already off');
			
			foreach ($this->storage[$this->depth] as $setter => $innerList) {
				Assert::isEqual(
					count($objectList),
					count($innerList) + array_sum($this->skipList[$this->depth])
				);
				
				$ids = array();
				
				foreach ($innerList as $inner)
					if ($inner)
						$ids[] = $inner->getId();
				
				// finding first available inner object
				foreach ($innerList as $inner)
					if ($inner)
						break;
				
				if (!$inner)
					continue;
				
				$dao = $inner->dao();
				
				// put yet unmapped objects into dao's identityMap
				$dao->getListByIds($ids);
				
				$i = 0;
				
				foreach ($objectList as $object) {
					if (isset($this->skipList[$this->depth][$object->getId()]))
						continue;
					
					if ($innerList[$i])
						$object->$setter(
							$dao->getById(
								$innerList[$i]->getId()
							)
						);
					
					++$i;
				}
			}
			
			unset($this->skipList[$this->depth], $this->storage[$this->depth--]);
			
			return $objectList;
		}
		
		public static function makeOnlyObject($className, $array, $prefix = null)
		{
			return self::assemblyObject(new $className, false, $array, $prefix);
		}
		
		public static function completeObject(Prototyped $object)
		{
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
			if ($property = $this->safePropertyGet($name))
				return $property;
			
			throw new MissingElementException(
				"unknown property requested by name '{$name}'"
			);
		}
		
		public function isPropertyExists($name)
		{
			return $this->safePropertyGet($name) !== null;
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
			$proto = $object->proto();
			
			foreach ($proto->getPropertyList() as $property) {
				if (
					$property->getRelationId() == MetaRelation::ONE_TO_ONE
					&& ($property->getFetchStrategyId() != FetchStrategy::LAZY)
				) {
					$getter = $property->getGetter();
					$setter = $property->getSetter();
					
					if (($inner = $object->$getter()) instanceof DAOConnected) {
						if ($proto->depth)
							$proto->storage[$proto->depth][$setter][] = $inner;
						else
							$object->$setter(
								$inner->dao()->getById($inner->getId())
							);
					} elseif (
						$proto->depth
						// emulating 'instanceof DAOConnected'
						&& method_exists($property->getClassName(), 'dao')
					)
						$proto->storage[$proto->depth][$setter][] = null;
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
						$columnName = $prefix.$property->getColumnName();
						
						if (
							$property->getFetchStrategyId()
							== FetchStrategy::LAZY
						) {
							$object->
								{$property->getSetter().'Id'}($array[$columnName]);
							
							continue;
						}
						
						$className = $property->getClassName();
						
						Assert::classExists($className);
						
						$isEnum = (
							$className
							&& is_subclass_of($className, 'Enumeration')
						);
						
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
		
		private function safePropertyGet($name)
		{
			$list = $this->getPropertyList();
			
			if (isset($list[$name]))
				return $list[$name];
			
			return null;
		}
	}
?>