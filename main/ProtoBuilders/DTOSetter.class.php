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

	final class DTOSetter extends PrototypedSetter
	{
		public function set($name, $value)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];
			
			$setter = 'set'.ucfirst($primitive->getName());
			
			if (!method_exists($this->object, $setter))
				throw new WrongArgumentException(
					"cannot find mutator for '$name' in class "
					.get_class($this->object)
				);
			
			if (is_object($value)) {
				
				if (
					($primitive instanceof PrimitiveAnyType)
					&& ($value instanceof DTOPrototyped)
				)
					$value = $value->dtoProto()->makeDto($value);
				else
					$value = $this->dtoValue($value);
				
			} elseif (is_array($value) && is_object(current($value))) {
				
				$dtoValue = array();
				
				foreach ($value as $oneValue) {
					Assert::isTrue(
						is_object($oneValue),
						'array must contain only objects'
					);
					
					$dtoValue[] = $this->dtoValue($oneValue);
				}
				
				$value = $dtoValue;
			}
			
			return $this->object->$setter($value);
		}
		
		private function dtoValue($value)
		{
			$result = null;
			
			if ($value instanceof DTOClass) {
				
				$result = $value; // have been already built
				
			} elseif ($value instanceof Identifiable) {
				
				$result = $value->getId();
				
			} elseif (
				$value instanceof Stringable
			) {
				$result = $value->toString();
				
			} else
				throw new WrongArgumentException(
					'don\'t know how to convert to DTO value of class '
					.get_class($value)
				);
			
			return $result;
		}
	}
?>