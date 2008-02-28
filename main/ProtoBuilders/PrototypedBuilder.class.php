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
		protected $proto	= null;
		
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
		
		/**
		 * @return PrototypedBuilder
		**/
		public function cloneBuilder(DTOProto $proto)
		{
			return new $this($proto);
		}
		
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
				
				if ($proto !== $objectProto) {
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
			
			foreach ($this->proto->getFormMapping() as $id => $primitive) {
				
				$value = $getter->get($id);
				
				// NOTE: NULL means the lack of optional value
				if ($primitive->isRequired() || $value !== null) {
					
					if ($primitive instanceof PrimitiveForm) {
						$proto = $primitive->getProto();
						
						if ($primitive instanceof PrimitiveFormsList) {
							$value = $this->cloneBuilder($proto)->
								makeList($value);
							
						} else {
							$value = $this->cloneBuilder($proto)->
								make($value);
						}
					}
					
					$setter->set($id, $value);
				}
			}
			
			$this->preserveTypeLoss($result);
			
			return $result;
		}
	}
?>