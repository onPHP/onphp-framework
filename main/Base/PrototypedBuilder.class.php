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
		
		abstract protected function createResult();
		abstract protected function alterResult($result);
		
		/**
		 * @return PrototypedGetter
		**/
		abstract protected function getGetter($object);
		
		/**
		 * @return PrototypedSetter
		**/
		abstract protected function getSetter(&$object);
		
		abstract protected function preserveResultTypeLoss($result);
		
		public function __construct(DTOProto $proto)
		{
			$this->proto = $proto;
		}
		
		public function cloneBuilder(DTOProto $proto)
		{
			$result = new $this($proto);
			
			return $result;
		}
		
		final public function make($object, $polymorph = true)
		{
			if ($polymorph) {
				if ($this->proto->isAbstract())
					throw new WrongArgumentException(
						'cannot make from abstract proto '
						.get_class($proto)
					);
				
				if (($object instanceof DTOPrototyped)) {
					$proto = $this->proto;
					$objectProto = $object->dtoProto();
					
					if ($proto !== $objectProto) {
						if (!$objectProto->instanceOf($proto))
							throw new WrongArgumentException(
								'target proto '.get_class($objectProto)
								.' is not a child of '.get_class($proto)
							);
						
						$proto = $objectProto;
						
						return $this->cloneBuilder($proto)->
							make($object, false);
					}
				}
			}
			
			if ($this->proto->baseProto()) {
				$result =
					$this->cloneBuilder(
						$this->proto->baseProto()
					)->
					make($object, false);
				
				$result = $this->alterResult($result);
				
			} else
				$result = $this->createResult();
			
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
								makeList($value, true);
							
						} else {
							$value = $this->cloneBuilder($proto)->
								make($value, true);
						}
					}
					
					$setter->set($id, $value);
				}
			}
			
			$this->preserveResultTypeLoss($result);
			
			return $result;
		}
		
		final public function makeList($objectsList)
		{
			if ($objectsList === null)
				return null;
			
			Assert::isArray($objectsList);
			
			$result = array();
			
			foreach ($objectsList as $object) {
				$result[] = $this->make($object, true);
			}
			
			return $result;
		}
	}
?>