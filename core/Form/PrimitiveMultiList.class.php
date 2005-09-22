<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Anton Lebedevich                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

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

					if (count($values))
						$this->value = $values;
					else
						return false;

				} else {
					if (isset($this->list[$scope[$this->name]]))
						$this->value = $scope[$this->name];
					else
						return false;
				}
			} else {
				$this->value = $scope[$this->name];
				
				return true;
			}

			/* NOTREACHED */
		}
	}
?>