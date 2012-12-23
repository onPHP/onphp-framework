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

	final class DTOGetter extends PrototypedGetter
	{
		private $soapDto	= true;
		
		public function __construct(EntityProto $proto, $object)
		{
			Assert::isInstance($object, 'DTOClass');
			
			return parent::__construct($proto, $object);
		}
		
		/**
		 * @return DTOGetter
		**/
		public function setSoapDto($soapDto)
		{
			$this->soapDto = ($soapDto === true);
			
			return $this;
		}
		
		// FIXME: isSoapDto()
		public function getSoapDto()
		{
			return $this->soapDto;
		}
		
		public function get($name)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];
			
			$method = 'get'.ucfirst($primitive->getName());
			
			$result = $this->object->$method();
			
			// TODO: primitives refactoring
			if (
				$result !== null
				&& $this->soapDto
				&& !is_array($result)
				&& (
					($primitive instanceof PrimitiveFormsList)
					|| ($primitive instanceof PrimitiveEnumerationList)
					|| ($primitive instanceof PrimitiveIdentifierList)
					|| ($primitive instanceof PrimitiveArray)
				)
			) {
				$result = array($result);
			}
			
			return $result;
		}
	}
