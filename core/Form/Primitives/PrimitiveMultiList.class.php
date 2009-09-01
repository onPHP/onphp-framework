<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Anton E. Lebedevich                        *
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
	final class PrimitiveMultiList extends PrimitiveList
	{
		private $selected = array();
		
		public function getChoiceValue()
		{
			return $this->selected;
		}
		
		public function import(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if (!$this->list)
				throw new WrongStateException(
					'list to check is not set; '
					.'use PrimitiveArray in case it is intentional'
				);
			
			if (is_array($scope[$this->name])) {
				$values = array();
				
				foreach ($scope[$this->name] as $value) {
					if (isset($this->list[$value])) {
						$values[] = $value;
						$this->selected[$value] = $this->list[$value];
					}
				}
				
				if (count($values)) {
					$this->value = $values;
					
					return true;
				}
			} elseif (!empty($scope[$this->name])) {
				$this->value = array($scope[$this->name]);
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * @return PrimitiveMultiList
		**/
		public function clean()
		{
			$this->selected = array();
			
			return parent::clean();
		}
		
		public function exportValue()
		{
			throw new UnimplementedFeatureException();
		}
	}
?>