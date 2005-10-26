<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Sveta Smirnova     *
 *   voxus@gentoo.org, sveta@microbecal.com                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PrimitiveString extends FiltrablePrimitive
	{
		private $pattern = null;
		
		public function setAllowedPattern($pattern)
		{
			$this->pattern = $pattern;
			
			return $this;
		}

		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (is_string($scope[$this->name]) && !empty($scope[$this->name]) &&
				!($this->max && mb_strlen($scope[$this->name]) > $this->max) &&
				!($this->min && mb_strlen($scope[$this->name]) < $this->min) &&
				(!$this->pattern || preg_match($this->pattern, $scope[$this->name]))
			) {
				$this->value = (string) $scope[$this->name];
				
				$this->selfFilter();

				return true;
			}

			return false;
		}
	}
?>
