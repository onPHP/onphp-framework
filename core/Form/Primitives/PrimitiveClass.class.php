<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveClass extends PrimitiveString
	{
		public function import(array $scope)
		{
			if (!($result = parent::import($scope)))
				return $result;
			
			if (
				!ClassUtils::isClassName($scope[$this->name])
				|| !$this->classExists($scope[$this->name])
			) {
				$this->value = null;
				
				return false;
			}
			
			return true;
		}
		
		private function classExists($name)
		{
			try {
				return class_exists($name, true);
			} catch (ClassNotFoundException $e) {
				return false;
			}
		}
	}
?>