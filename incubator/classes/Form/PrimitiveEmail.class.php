<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Holds validated e-mail
	 * 
	 * Attention! Validation algoritm is primitive
	 * @package		Form
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	 * @obsoleted	by PrimitiveString
	**/
	class PrimitiveEmail extends PrimitiveString
	{
		/**
		 * Checks $scope[$this->name] is possible e-mail before importing
		 * 
		 * @param	array	associative array
		 * @access	public
		 * @return	boolean
		**/
		public function import(&$scope) // TODO: consider checkMail from MiscUtils
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (
				is_string($scope[$this->name]) && !empty($scope[$this->name])
				&& !($this->max && strlen($scope[$this->name]) > $this->max)
				&& preg_match('/^[^@]*\w+@[\w|\.|\-]+\.\w{2,6}$/', $scope[$this->name])
			)
			{
				$this->value = $scope[$this->name];
				return true;
			}

			return false;
		}
	}
?>