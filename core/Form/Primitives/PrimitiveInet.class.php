<?php
/****************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                           *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveInet extends BasePrimitive
	{
		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if (
				is_string($scope[$this->name])
				&& (substr_count($scope[$this->name], '.', null, 15) == 3)
				&& long2ip(ip2long($scope[$this->name]))
			) {
				$this->value = $scope[$this->name];
				
				return $this->imported = true;
			}
			
			return false;
		}
	}
?>