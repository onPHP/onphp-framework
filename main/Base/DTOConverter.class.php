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

	abstract class DTOConverter
	{
		private $soapDto = false;
		
		abstract public function createResult();
		abstract public function preserveTypeLoss($value, DTOProto $childProto);
		abstract public function saveToResult(
			$value, BasePrimitive $primitive, $result
		);
		
		public function __construct(DTOProto $proto)
		{
			$this->proto = $proto;
		}
		
		public function setSoapDto($soapDto)
		{
			$this->soapDto = ($soapDto === true);
			
			return $this;
		}
		
		public function getSoapDto()
		{
			return $this->soapDto;
		}
		
		public function cloneConverter(DTOProto $proto)
		{
			$result = new $this($proto);
			
			$result->setSoapDto($this->soapDto);
			
			return $result;
		}
		
		final public function convertDto(DTOClass $dto)
		{
			$dtoClass = $this->proto->dtoClassName();
			Assert::isInstance($dto, $dtoClass);
			
			if ($this->proto->baseProto()) {
				$result =
					$this->cloneConverter(
						$this->proto->baseProto()
					)->
					convertDto($dto);
				
				$this->proto->attachPrimitives($result);
				
			} else
				$result = $this->createResult();
				
			foreach ($this->proto->getFormMapping() as $primitive) {
				
				$methodName = 'get'.ucfirst($primitive->getName());
				$value = $dto->$methodName();
				
				// NOTE: NULL means the lack of optional value
				if ($primitive->isRequired() || $value !== null) {
					
					if ($this->soapDto)
						$value = $this->
							soapSingleItemToArray($primitive, $value);
					
					if ($primitive instanceof PrimitiveForm) {
						
						$proto = Singleton::getInstance(
							DTOProto::PROTO_CLASS_PREFIX.$primitive->getClassName()
						);
						
						if ($primitive instanceof PrimitiveFormsList) {
							$value = $this->cloneConverter($proto)->
								convertDtosList($value);
							
						} else {
							
							$childType = false;
							
							if ($value) {
								$protoDtoClass = $proto->dtoClassName();
							
								Assert::isInstance($value, $protoDtoClass);
								
								if (get_class($value)!== $protoDtoClass) {
									$proto = $value->dtoProto();
									$childType = true;
								}
							}
							
							if ($proto->isAbstract())
								throw new WrongArgumentException(
									'cannot convert from '
									.'abstract proto for class '
									.get_class($value)
								);
							
							$value = $this->cloneConverter($proto)->
								convertDto($value);
							
							$this->preserveTypeLoss($value, $proto);
						}
					}
					
					$this->saveToResult($value, $primitive, $result);
				}
			}
			
			return $result;
		}
		
		final public function convertDtosList($dtosList)
		{
			if ($dtosList === null)
				return null;
			
			Assert::isArray($dtosList);
			
			$result = array();
			
			foreach ($dtosList as $dto) {
				$result[] = $this->convertDto($dto);
			}
			
			return $result;
		}
		
		private function soapSingleItemToArray(BasePrimitive $primitive, $value)
		{
			// TODO: primitives refactoring
			if (
				($primitive instanceof PrimitiveFormsList)
				|| ($primitive instanceof PrimitiveEnumerationList)
				|| ($primitive instanceof PrimitiveIdentifierList)
				|| ($primitive instanceof PrimitiveArray)
			) {
				if (!is_array($value))
					$value = array($value);
			}
			
			return $value;
		}
	}
?>