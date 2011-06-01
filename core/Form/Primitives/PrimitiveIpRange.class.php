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
	final class PrimitiveIpRange extends BasePrimitive
	{
		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if ($scope[$this->getName()] instanceof IpRange) {
				$this->value = $scope[$this->getName()];
				
				return true;
			}
			
			try {
				$this->value = IpRange::create($scope[$this->getName()]);
				
				return true;
			} catch (WrongArgumentException $e) {
				return false;
			}
			
			Assert::isUnreachable();
		}
		
		public function setValue(/*IpRange*/ $value)
		{
			Assert::isInstance($value, 'IpRange');
			
			$this->value = $value;
			
			return $this;
		}
		
		public function setDefault($default)
		{
			Assert::isInstance($default, 'IpRange');
			
			$this->default = $default;
			
			return $this;
		}
	}
?>
