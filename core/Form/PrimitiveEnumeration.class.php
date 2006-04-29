<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveEnumeration extends PrimitiveInteger
	{
		private $class = null;
		
		public function setValue($value)
		{
			$className = $this->class->getName();
			
			Assert::isTrue($value instanceof $className);
			
			return parent::setValue($value);
		}
		
		public function of($class)
		{
			Assert::isTrue(
				class_exists($class, true),
				"knows nothing about '{$class}' class"
			);
			
			// TODO: assert class named $class is instance of Enumeration
			
			$this->class = $class;
			
			return $this;
		}
		
		public function import(&$scope)
		{
			if (!$this->class)
				throw new WrongStateException(
					"no class defined for PrimitiveEnumeration '{$this->name}'"
				);
				
			$result = parent::import($scope);
				
			if ($result === true) {
				try {
					$class = $this->class;
					$this->value = new $class($this->value);
				} catch (MissingElementException $e) {
					return false;
				}
				
				return true;
			}
			
			return $result;
		}
	}
?>
