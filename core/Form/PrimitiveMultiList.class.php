<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveMultiList extends PrimitiveList
	{
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if ($this->list) {
				if (is_array($scope[$this->name])) {
					$values = array();

					foreach ($scope[$this->name] as $value)
						if (isset($this->list[$value]))
							$values[] = $value;

					if (count($values)) {
						$this->value = $values;
						
						return true;
					}
				} else {
					if (isset($this->list[$scope[$this->name]])) {
						$this->value = $scope[$this->name];
						
						return true;
					}
				}
			} else {
				$this->value = $scope[$this->name];
				
				return true;
			}

			return false;
		}
	}
?>