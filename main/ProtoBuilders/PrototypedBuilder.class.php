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
		abstract protected function prepareOwn($result);
		
		/**
		 * @return PrototypedGetter
		**/
		abstract protected function getGetter($object);
		
		/**
		 * @return PrototypedSetter
		**/
		abstract protected function getSetter(&$object);
		
		/**
		 * @return PrototypedBuilder
		**/
		abstract protected function preserveTypeLoss($result);
		
		public function __construct(DTOProto $proto)
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
		public function cloneBuilder(DTOProto $proto)
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
		
		/**
		 * @deprecated $recursive, use limitedPropertiesList instead
		 */
		public function make($object, $recursive = true)
		{
			// FIXME: make dtoProto() non-static, problem with forms here
			if (
				($object instanceof DTOPrototyped)
				|| ($object instanceof Form)
			) {
				$proto = $this->proto;
				
				if ($object instanceof Form) {
					$objectProto = $object->getProto();
				} else
					$objectProto = $object->dtoProto();
				
				if (!ClassUtils::isInstanceOf($proto, $objectProto)) {
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
			
			$result = $this->createEmpty();
			
			if ($recursive)
				$result = $this->upperMake($object, $result);
			else
				$result = $this->makeOwn($object, $result);
			
			return $result;
		}
		
		public function upperMake($object, &$result)
		{
			if ($this->proto->baseProto()) {
				$result =
					$this->cloneBuilder(
						$this->proto->baseProto()
					)->
					upperMake($object, $result);
			}
			
			return $this->makeOwn($object, $result);
		}
		
		public function makeOwn($object, &$result)
		{
			$result = $this->prepareOwn($result);
			$result = $this->fillOwn($object, $result);
			
			return $result;
		}
		
		public function makeList($objectsList)
		{
			if ($objectsList === null)
				return null;
			
			Assert::isArray($objectsList);
			
			$result = array();
			
			foreach ($objectsList as $object) {
				$result[] = $this->make($object);
			}
			
			return $result;
		}
		
		public function fillOwn($object, $result)
		{
			$getter = $this->getGetter($object);
			$setter = $this->getSetter($result);
			
			foreach ($this->getFormMapping() as $id => $primitive) {
				
				$value = $getter->get($id);
				
				// NOTE: NULL means the lack of optional value
				if ($primitive->isRequired() || $value !== null) {
					
					
					if ($primitive instanceof PrimitiveForm) {
						if ($primitive instanceof PrimitiveFormsList) {
							$value = $this->cloneInnerBuilder($id)->
								makeList($value);
							
						} else {
							$value = $this->cloneInnerBuilder($id)->
								make($value);
						}
					}
					
					$setter->set($id, $value);
				}
			}
			
			$this->preserveTypeLoss($result);
			
			return $result;
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