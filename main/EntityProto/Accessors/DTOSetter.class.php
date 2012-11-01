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

	namespace Onphp;

	final class DTOSetter extends PrototypedSetter
	{
		private $getter = null;
		
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
					&& ($value instanceof PrototypedEntity)
				)
					$value =
						ObjectToDTOConverter::create($value->entityProto())->
							make($value);
				else
					$value = $this->dtoValue($value, $primitive);
				
			} elseif (is_array($value) && is_object(current($value))) {
				
				$dtoValue = array();
				
				foreach ($value as $oneValue) {
					Assert::isTrue(
						is_object($oneValue),
						'array must contain only objects'
					);
					
					$dtoValue[] = $this->dtoValue($oneValue, $primitive);
				}
				
				$value = $dtoValue;
			}
			
			return $this->object->$setter($value);
		}
		
		// TODO: use export for all primitives
		private function dtoValue($value, BasePrimitive $primitive)
		{
			$result = null;
			
			if ($value instanceof DTOClass) {
				
				$result = $value; // have been already built
				
			} elseif ($primitive instanceof PrimitivePolymorphicIdentifier) {
				
				$result = PrimitivePolymorphicIdentifier::export($value);
				
			} elseif ($primitive instanceof PrimitiveBoolean) {
				
				$result = (boolean)$value;
				
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
		
		/**
		 * @return \Onphp\DTOGetter
		**/
		public function getGetter()
		{
			if (!$this->getter) {
				$this->getter = new DTOGetter($this->proto, $this->object);
			}
			
			return $this->getter;
		}
	}
?>