<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveIdentifierList extends PrimitiveIdentifier
	{
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifierList '{$this->name}'"
				);
			
			if (!BasePrimitive::import($scope))
				return null;
			
			if (is_array($scope[$this->name])) {
				$scope[$this->name] =
					array_unique($scope[$this->name]);
				
				$values = array();
				
				foreach ($scope[$this->name] as $value)
					$values[] = $value;
				
				if (count($values)) {
					$objectList =
						$this->dao()->getListByIds($values);
					
					if (count($objectList) == count($values)) {
						$this->value = $objectList;
						return true;
					} else {
						$this->value = null;
						return false;
					}
				}
				
				return true;
			} else {
				return parent::import($scope);
			}
			
			return false;
		}
	}
?>