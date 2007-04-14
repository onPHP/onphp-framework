<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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
	final class PrimitivesBox extends BasePrimitive
	{
		private $list = array();
		
		public function getDefault()
		{
			throw new UnsupportedMethodException();
		}
		
		public function setDefault($default)
		{
			throw new UnsupportedMethodException();
		}
		
		public function getActualValue()
		{
			throw new UnsupportedMethodException();
		}
		
		public function getSafeValue()
		{
			throw new UnsupportedMethodException();
		}
		
		public function setValue($value)
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function get($name)
		{
			if (!isset($this->list[$name]))
				throw new MissingElementException('no "'.$name.'" in a box');
			
			return $this->list[$name];
		}
		
		/**
		 * @return PrimitivesBox
		**/
		public function add(BasePrimitive $prm)
		{
			Assert::isFalse(isset($this->list[$prm->getName()]));
			
			$this->list[$prm->getName()] = $prm;
			
			return $this;
		}
		
		/**
		 * @return PrimitivesBox
		**/
		public function drop($name)
		{
			if (!isset($this->list[$name]))
				throw new MissingElementException('no "'.$name.'" in a box');
			
			unset($this->list[$name]);
			
			return $this;
		}
		
		public function isImported()
		{
			return $this->imported;
		}
		
		public function importValue($value)
		{
			Assert::isArray($value);
			
			$result = false;
			
			// logic is reversed to speed up import a bit
			foreach ($this->list as $name => $prm)
				if (array_key_exists($name, $value))
					$result = $prm->importValue($value) || $result;
			
			return $result;
		}
		
		public function import($scope)
		{
			if (isset($scope[$this->name]) && is_array($scope[$this->name])) {
				foreach ($scope[$this->name] as $key => $raw) {
					if (isset($this->list[$key])) {
						if ($this->list[$key]->import($scope[$this->name])) {
							$this->imported = true;
						}
					}
				}
			} else {
				return false;
			}
			
			return $this->imported;
		}
	}
?>