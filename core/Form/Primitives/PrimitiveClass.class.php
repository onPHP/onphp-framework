<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveClass extends PrimitiveString
	{
		private $ofClassName = null;
		
		public function import(array $scope)
		{
			if (!($result = parent::import($scope)))
				return $result;
			
			if (
				!ClassUtils::isClassName($scope[$this->name])
				|| !$this->classExists($scope[$this->name])
				|| (
					$this->ofClassName
					&& !ClassUtils::isInstanceOf(
						$scope[$this->name],
						$this->ofClassName
					)
				)
			) {
				$this->value = null;
				
				return false;
			}
			
			return true;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveIdentifier
		**/
		public function of($class)
		{
			$className = $this->guessClassName($class);
			
			Assert::isTrue(
				class_exists($className, true)
				|| interface_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$this->ofClassName = $className;
			
			return $this;
		}
		
		private function classExists($name)
		{
			try {
				return class_exists($name, true);
			} catch (ClassNotFoundException $e) {
				return false;
			}
		}
		
		private function guessClassName($class)
		{
			if (is_string($class))
				return $class;
			
			elseif (is_object($class))
				return get_class($class);
			
			throw new WrongArgumentException('strange class given - '.$class);
		}
	}
?>