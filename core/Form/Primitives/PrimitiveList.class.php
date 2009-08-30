<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
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
	class PrimitiveList extends BasePrimitive implements ListedPrimitive
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
		
		public function import($scope)
		{
			if (!parent::import($scope)) {
				return null;
			}
			
			if (
				(
					is_string($scope[$this->name])
					|| is_integer($scope[$this->name])
				)
				&& isset($this->list[$scope[$this->name]])
			) {
				$this->value = $scope[$this->name];
				
				return true;
			} else
				return false;

			/* NOTREACHED */
		}
	}
?>