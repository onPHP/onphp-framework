<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveIdentifier extends IdentifiablePrimitive
	{
		public function of($className)
		{
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$class = new ReflectionClass($className);
			
			Assert::isTrue(
				$class->implementsInterface('DAOConnected'),
				"class '{$class->getName()}' should implement DAOConnected interface"
			);
			
			$this->className = $className;
			
			return $this;
		}
		
		public function dao()
		{
			return call_user_func(array($this->className, 'dao'));
		}
		
		public function import(&$scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifier '{$this->name}'"
				);
			
			$result = parent::import($scope);
				
			if ($result === true) {
				try {
					$this->value = $this->dao()->getById($this->value);
				} catch (ObjectNotFoundException $e) {
					$this->value = null;
					return false;
				}
				
				return true;
			}
			
			return $result;
		}
	}
?>