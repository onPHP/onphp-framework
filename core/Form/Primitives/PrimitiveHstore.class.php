<?php
/****************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                                *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveHstore extends FiltrablePrimitive
	{
		/**
		 * List of allowed keys.
		 * If list is empty - all keys is allowed.
		 *
		 * @var array
		**/
		protected $allowedKeys = array();
		
		/**
		 * @return PrimitiveHstore
		**/
		public function setAllowedKeys($array)
		{
			Assert::isArray($array);
			
			$this->allowedKeys = $array;
			
			return $this;
		}
		
		public function getAllowedKeys()
		{
			return $this->allowedKeys;
		}
		
		public function isCheckAllowedKeys()
		{
			return !empty($this->allowedKeys);
		}
		
		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			$this->value = $scope[$this->name];
			
			if (!$this->checkAllowedKeys()) {
				$this->value = null;
				return false;
			}
			
			$this->selfFilter();
			
			if (
				is_array($this->value)
				&& !($this->min && count($this->value) < $this->min)
				&& !($this->max && count($this->value) > $this->max)
			) {
				return true;
			} else {
				$this->value = null;
			}
			
			return false;
		}
		
		public function importValue($value)
		{
			if (
				is_array($value)
				&& $this->checkAllowedKeys()
			)
				return $this->import(array($this->getName() => $value));
			
			return false;
		}
		
		public function clean()
		{
			$this->allowedKeys = array();
			
			return parent::clean();
		}
		
		protected function checkAllowedKeys()
		{
			if (
				$this->isCheckAllowedKeys()
				&& is_array($this->value)
			)
				foreach ($this->value as $k => $v)
					if (!in_array($k, $this->allowedKeys))
						return false;
			
			return true;
		}
	}
?>