<?php
/****************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                           *
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
	final class PrimitiveInet extends BasePrimitive
	{
		public function import($scope, $prefix = null)
		{
			if (!BasePrimitive::import($scope, $prefix))
				return null;
			
			$name = $this->getActualName($prefix);
			
			if (
				is_string($scope[$name])
				&& (($length = strlen($scope[$name])) < 16)
				&& (substr_count($scope[$name], '.', null, $length) == 3)
				&& long2ip(ip2long($scope[$name]))
			) {
				$this->value = $scope[$name];
				
				return true;
			}
			
			return false;
		}
	}
?>