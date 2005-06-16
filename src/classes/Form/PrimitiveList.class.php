<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PrimitiveList extends BasePrimitive
	{
		protected $list = array();
		
		public function getList()
		{
			return $this->list;
		}
		
		public function setList($list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		public function import(&$scope)
		{
			if (!parent::import($scope)) {
				return null;
			}
			
			if (isset($this->list[$scope[$this->name]]))
				$this->value = $scope[$this->name];
			else
				return false;

			return true;
		}
	}
?>
