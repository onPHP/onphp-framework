<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveEnum extends IdentifiablePrimitive
	{
		public function getList()
		{
			if ($this->value)
				return ClassUtils::callStaticMethod(get_class($this->value).'::getObjectList');
			elseif ($this->default)
				return ClassUtils::callStaticMethod(get_class($this->default).'::getObjectList');
			else {
				$object = new $this->className(
					ClassUtils::callStaticMethod($this->className.'::getAnyId')
				);
				
				return $object->getObjectList();
			}
			
			Assert::isUnreachable();
		}

		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveEnum
		**/
		public function of($class)
		{
			$className = $this->guessClassName($class);
			
			Assert::classExists($className);
			
			Assert::isInstance($className, 'Enum');
			
			$this->className = $className;
			
			return $this;
		}
		
		public function importValue(/* Identifiable */ $value)
		{
			if ($value)
				Assert::isEqual(get_class($value), $this->className);
			else
				return parent::importValue(null);
			
			return $this->import(array($this->getName() => $value->getId()));
		}
		
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveEnum '{$this->name}'"
				);
			
			$result = parent::import($scope);
			
			if ($result === true) {
				try {
					$this->value = new $this->className($this->value);
				} catch (MissingElementException $e) {
					$this->value = null;
					
					return false;
				}
				
				return true;
			}
			
			return $result;
		}
	}
?>