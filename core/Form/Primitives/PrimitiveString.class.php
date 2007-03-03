<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Sveta Smirnova     *
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
	class PrimitiveString extends FiltrablePrimitive
	{
		private $pattern = null;
		
		/**
		 * mail hint: /^[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/
		 * 
		 * @return PrimitiveString
		**/
		public function setAllowedPattern($pattern)
		{
			$this->pattern = $pattern;
			
			return $this;
		}

		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
				
			$this->value = (string) $scope[$this->name];
			
			$this->selfFilter();

			if (!empty($scope[$this->name]) && is_string($scope[$this->name])
				&& !($this->max && mb_strlen($scope[$this->name]) > $this->max)
				&& !($this->min && mb_strlen($scope[$this->name]) < $this->min)
				&& (!$this->pattern || preg_match($this->pattern, $scope[$this->name]))
			) {
				return true;
			} else {
				$this->value = null;
			}

			return false;
		}
	}
?>