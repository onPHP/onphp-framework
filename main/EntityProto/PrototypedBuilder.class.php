<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class PrototypedBuilder
	{
		protected $proto		= null;
		
		private $limitedPropertiesList	= null;
		
		abstract protected function createEmpty();
		
		/**
		 * @return PrototypedGetter
		**/
		abstract protected function getGetter($object);
		
		/**
		 * @return PrototypedSetter
		**/
		abstract protected function getSetter(&$object);
		
		public function __construct(EntityProto $proto)
		{
			$this->proto = $proto;
		}
		
		public function setLimitedPropertiesList($list)
		{
			if ($list !== null)
				Assert::isArray($list);
			
			$mapping = $this->proto->getFullFormMapping();
			
			foreach ($list as $key => $inner)
				Assert::isIndexExists($mapping, $key);
				
			$this->limitedPropertiesList = $list;
			
			return $this;
		}
		
		/**
		 * @return PrototypedBuilder
		**/
		public function cloneBuilder(EntityProto $proto)
		{
			Assert::isTrue(
				$this->proto->isInstanceOf($proto)
				|| $proto->isInstanceOf($this->proto),
				
				Assert::dumpArgument($proto)
			);
			
			$result = new $this($proto);
			
			$result->limitedPropertiesList = $this->limitedPropertiesList;
			
			return $result;
		}
		
		public function cloneInnerBuilder($property)
		{
			$mapping = $this->getFormMapping();
			
			Assert::isIndexExists($mapping, $property);
			
			$primitive = $mapping[$property];
			
			Assert::isInstance($primitive, 'PrimitiveForm');
			
			$result = new $this($primitive->getProto());
			
			if (isset($this->limitedPropertiesList[$primitive->getName()])) {
				$result->setLimitedPropertiesList(
					$this->limitedPropertiesList[$primitive->getName()]
				);
			}
			
			return $result;
		}

		public function makeListItemBuilder($object)
		{
			return $this;
		}
		
		/**
		 * @return PrototypedBuilder
		**/
		public function makeReverseBuilder()
		{
			throw new UnimplementedFeatureException(
				'reverse builder is not provided yet'
			);
		}

		/**
		 * Also try using plain limitedPropertiesList instead of limited
		 * hierarchy recursing.
		**/
		public function make($object, $recursive = true)
		{
			// FIXME: make entityProto() non-static, problem with forms here
			if (
				($object instanceof PrototypedEntity)
				|| ($object instanceof Form)
			) {
				$proto = $this->proto;
				
				if ($object instanceof Form) {
					$objectProto = $object->getProto();
				} else
					$objectProto = $object->entityProto();
				
				if (
					$objectProto
					&& !ClassUtils::isInstanceOf($proto, $objectProto)
				) {
					if (!$objectProto->isInstanceOf($proto))
						throw new WrongArgumentException(
							'target proto '.get_class($objectProto)
							.' is not a child of '.get_class($proto)
						);
					
					$proto = $objectProto;
					
					return $this->cloneBuilder($proto)->
						make($object);
				}
			}
			
			if ($this->proto->isAbstract())
				throw new WrongArgumentException(
					'cannot make from abstract proto '
					.get_class($this->proto)
				);
			
			return $this->compile($object, $recursive);
		}
		
		public function compile($object, $recursive = true)
		{
			$result = $this->createEmpty();

			$this->initialize($object, $result);

			if ($recursive)
				$result = $this->upperMake($object, $result);
			else
				$result = $this->makeOwn($object, $result);
			
			return $result;
		}
		
		public function upperMake($object, &$result)
		{
			if ($this->proto->baseProto()) {
				$this->cloneBuilder($this->proto->baseProto())->
					upperMake($object, $result);
			}
			
			return $this->makeOwn($object, $result);
		}
		
		public function makeList($objectsList)
		{
			if ($objectsList === null)
				return null;
			
			Assert::isArray($objectsList);
			
			$result = array();
			
			foreach ($objectsList as $object) {
				$result[] = $this->makeListItemBuilder($object)->
					make($object);
			}
			
			return $result;
		}
		
		public function makeOwn($object, &$result)
		{
			return $this->fillOwn($object, $result);
		}
		
		public function upperFill($object, &$result)
		{
			if ($this->proto->baseProto()) {
				$this->cloneBuilder($this->proto->baseProto())->
					upperFill($object, $result);
			}
			
			return $this->fillOwn($object, $result);
		}
		
		public function fillOwn($object, &$result)
		{
			if ($object === null)
				return $result;
			
			$getter = $this->getGetter($object);
			$setter = $this->getSetter($result);
			
			foreach ($this->getFormMapping() as $id => $primitive) {

				$value = $getter->get($id);
				
				if ($primitive instanceof PrimitiveFormsList) {
						
					$setter->set(
						$id,
						$this->cloneInnerBuilder($id)->
							makeList($value)
					);
					
				} elseif ($primitive instanceof PrimitiveForm) {
					
					if (
						$primitive->isComposite()
						&& ($previousValue = $setter->getGetter()->get($id))
					) {
						
						$this->cloneInnerBuilder($id)->
							upperFill($value, $previousValue);
						
					} elseif ($value !== null || $primitive->isRequired()) {
						
						$setter->set(
							$id,
							$this->cloneInnerBuilder($id)->
								make($value)
						);
					}
				
				} else {
					$setter->set($id, $value);
				}
			}
			
			return $result;
		}
		
		protected function initialize($object, &$result)
		{
			return $this;
		}

		protected function getFormMapping()
		{
			$protoMapping = $this->proto->getFormMapping();
			
			if ($this->limitedPropertiesList === null)
				return $protoMapping;
			
			$result = array();
			
			foreach ($protoMapping as $id => $value) {
				if (!isset($this->limitedPropertiesList[$id]))
					continue;
				
				$result[$id] = $value;
			}
			
			return $result;
		}
	}
?>