<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveList extends BasePrimitive implements ListedPrimitive
	{
		protected $list = array();
		
		public function getChoiceValue()
		{
			if ($this->value !== null)
				return $this->list[$this->value];
			
			return null;
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return PrimitiveList
		**/
		public function setList(array $list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		public function import(array $scope)
		{
			if (!parent::import($scope)) {
				return null;
			}
			
			if (
				(
					is_string($scope[$this->name])
					|| is_integer($scope[$this->name])
				)
				&& array_key_exists($scope[$this->name], $this->list)
			) {
				$this->value = $scope[$this->name];
				
				return true;
			}
			
			return false;
		}
	}
?>