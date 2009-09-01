<?php
/****************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                      *
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
	final class PrimitiveInet extends BasePrimitive
	{
		public function import(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if (
				is_string($scope[$this->name])
				&& (($length = strlen($scope[$this->name])) < 16)
				&& (substr_count($scope[$this->name], '.', null, $length) == 3)
				&& long2ip(ip2long($scope[$this->name]))
			) {
				$this->value = $scope[$this->name];
				
				return true;
			}
			
			return false;
		}
	}
?>