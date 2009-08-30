<?php
/****************************************************************************
 *   Copyright (C) 2007-2008 by Denis M. Gabaidulin, Konstantin V. Arkhipov *
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
			
			if (!is_array($scope[$this->name]))
				return false;
			
			$list = array_unique($scope[$this->name]);
			
			$values = array();
			
			foreach ($list as $id) {
				if (!Assert::checkInteger($id))
					return false;
				
				$values[] = $id;
			}
			
			$objectList = $this->dao()->getListByIds($values);
			
			if (
				count($objectList) == count($values)
				&& !($this->min && count($values) < $this->min)
				&& !($this->max && count($values) > $this->max)
			) {
				$this->value = $objectList;
				
				return true;
			}
			
			return false;
		}
	}
?>