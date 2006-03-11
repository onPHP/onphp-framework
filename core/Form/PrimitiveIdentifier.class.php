<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
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
	final class PrimitiveIdentifier extends PrimitiveInteger
	{
		private $class	= null;
		
		public function setValue($value)
		{
			Assert::isTrue($value instanceof $this->class);
			
			return parent::setValue($value);
		}
		
		public function of($class)
		{
			Assert::isTrue(
				class_exists($class, true),
				"knows nothing about '{$class}' class"
			);
			
			$class = new ReflectionClass($class);
			
			Assert::isTrue(
				$class->implementsInterface('DAOConnected'),
				"class '{$class->getName()}' should implement DAOConnected interface"
			);
			
			$this->class = $class;
			
			return $this;
		}
		
		public function import(&$scope)
		{
			if (!$this->class)
				throw new WrongStateException(
					"no defined class for PrimitiveIdentifier '{$this->name}'"
				);
			
			if (parent::import($scope)) {
				
				$dao = call_user_func(array($this->class->getName(), 'dao'));
				
				try {
					$this->value = $dao->getById($this->value);
				} catch (ObjectNotFoundException $e) {
					return false;
				}
				
				return true;
			}
			
			return false;
		}
	}
?>