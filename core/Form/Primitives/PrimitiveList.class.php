<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
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
	class PrimitiveList extends BasePrimitive implements ListedPrimitive
	{
		protected $list = array();
		
		public function getChoiceValue()
		{
			if ($this->value !== null)
				return $this->list[$this->value];
			
			return null;
		}
		
		public function getActualChoiceValue()
		{
			if ($this->value !== null)
				return $this->list[$this->value];
			
			return $this->list[$this->default];
		}
		
		/**
		 * @return PrimitiveList
		**/
		public function setDefault($default)
		{
			Assert::isTrue(
				$this->list
				&& array_key_exists(
					$default,
					$this->list
				),
				
				'can not find element with such index'
			);
			
			return parent::setDefault($default);
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return PrimitiveList
		**/
		public function setList($list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		public function import($scope, $prefix = null)
		{
			if (!parent::import($scope, $prefix)) {
				return null;
			}
			
			$name = $this->getActualName($prefix);
			
			if (
				(is_string($scope[$name]) || is_integer($scope[$name]))
				&& array_key_exists($scope[$name], $this->list)
			) {
				$this->value = $scope[$name];
				
				return true;
			}
			
			return false;
		}
	}
?>