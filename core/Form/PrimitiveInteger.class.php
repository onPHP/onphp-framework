<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveInteger extends FiltrablePrimitive
	{
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			try {
				Assert::isInteger($scope[$this->name]);
			} catch (WrongArgumentException $e) {
				return false;
			}
			
			if (
				!(null !== $this->min && $scope[$this->name] < $this->min) &&
				!(null !== $this->max && $scope[$this->name] > $this->max)
			) {
				$this->value = (int) $scope[$this->name];

				$this->selfFilter();
				
				return true;
			}
			
			return false;
		}
	}
?>