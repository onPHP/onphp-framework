<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	abstract class BaseObjectPrimitive extends BasePrimitive
	{
		protected $className = null;
		
		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if ($scope[$this->getName()] instanceof $this->className) {
				$this->value = $scope[$this->getName()];
				
				return true;
			}
			
			try {
				$this->value = new $this->className($scope[$this->getName()]);
				
				return true;
			} catch (WrongArgumentException $e) {
				return false;
			}
			
			Assert::isUnreachable();
		}
		
		public function setValue($value)
		{
			Assert::isInstance($value, $this->className);
			
			$this->value = $value;
			
			return $this;
		}
		
		public function setDefault($default)
		{
			Assert::isInstance($default, $this->className);
			
			$this->default = $default;
			
			return $this;
		}

		public function exportValue()
		{
			if ($this->value instanceof Stringable) {
				return $this->value->toString();
			}

			throw new UnimplementedFeatureException('dont know how to export ' . $this->className);
		}

	}
?>