<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
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
	final class PrimitiveBinary extends FiltrablePrimitive
	{
		public function import(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			$this->value = (string) $scope[$this->name];
			
			$this->selfFilter();
			
			if (!empty($this->value) && is_string($this->value)
				&& ($length = strlen($this->value))
				&& !($this->max && $length > $this->max)
				&& !($this->min && $length < $this->min)
			) {
				return true;
			} else {
				$this->value = null;
			}
			
			return false;
		}
	}
?>