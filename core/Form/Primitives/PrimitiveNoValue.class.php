<?php
/****************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                              *
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
	final class PrimitiveNoValue extends BasePrimitive
	{
		/**
		 * @return PrimitiveNoValue
		**/
		public function setValue($value)
		{
			Assert::isUnreachable('No value!');
			
			return $this;
		}
		
		public function setRawValue($raw)
		{
			Assert::isUnreachable('No raw value!');
			
			return $this;
		}
		
		public function importValue($value)
		{
			Assert::isUnreachable('No import value!');
			
			return $this;
		}
		
		public function import(array $scope)
		{
			if (
				key_exists($this->name, $scope)
				&& $scope[$this->name] == null
			)
				return $this->imported = true;
			
			return null;
		}
	}
?>