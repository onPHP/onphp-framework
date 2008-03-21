<?php
/****************************************************************************
 *   Copyright (C) 2006-2007 by Dmitry E. Demidov                           *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveTernary extends BasePrimitive
	{
		private $falseValue		= 0;
		private	$trueValue		= 1;
		
		/**
		 * @return PrimitiveTernary
		**/
		public function setTrueValue($trueValue)
		{
			$this->trueValue = $trueValue;
			
			return $this;
		}
		
		/**
		 * @return PrimitiveTernary
		**/
		public function setFalseValue($falseValue)
		{
			$this->falseValue = $falseValue;
			
			return $this;
		}
		
		public function import($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (isset($scope[$name])) {
				if ($this->trueValue == $scope[$name])
					$this->value = true;
				elseif ($this->falseValue == $scope[$name])
					$this->value = false;
				else
					return false;
			} else {
				$this->clean();
				
				return null;
			}
			
			$this->raw = $scope[$name];
			
			return $this->imported = true;
		}
		
		public function importValue($value)
		{
			Assert::isTernaryBase($value, 'only ternary based accepted');
			
			$this->value = $value;
			
			return $this->imported = true;
		}
	}
?>