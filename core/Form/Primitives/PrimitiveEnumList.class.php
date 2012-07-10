<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
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
	final class PrimitiveEnumList extends PrimitiveEnum
	{
		protected $value = array();
		
		/**
		 * @return PrimitiveEnumList
		**/
		public function clean()
		{
			parent::clean();
			
			// restoring our very own default
			$this->value = array();
			
			return $this;
		}
		
		/**
		 * @return PrimitiveEnumList
		**/
		public function setValue(/* Enum */ $value)
		{
			if ($value) {
				Assert::isArray($value);
				Assert::isInstance(current($value), 'Enum');
			}
			
			$this->value = $value;
			
			return $this;
		}
		
		public function importValue($value)
		{
			if (is_array($value)) {
				try {
					Assert::isInteger(current($value));
					
					return $this->import(
						array($this->name => $value)
					);
				} catch (WrongArgumentException $e) {
					return $this->import(
						array($this->name => ArrayUtils::getIdsArray($value))
					);
				}
			}
			
			return parent::importValue($value);
		}
		
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifierList '{$this->name}'"
				);
			
			if (!BasePrimitive::import($scope))
				return null;
			
			if (!is_array($scope[$this->name]))
				return false;
			
			$list = array_unique($scope[$this->name]);
			
			$values = array();
			
			foreach ($list as $id) {
				if (!Assert::checkInteger($id))
					return false;
				
				$values[] = $id;
			}
			
			$objectList = array();
			
			foreach ($values as $value) {
				$className = $this->className;
				$objectList[] = new $className($value);
			}
			
			if (count($objectList) == count($values)) {
				$this->value = $objectList;
				return true;
			}
			
			return false;
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return ArrayUtils::getIdsArray($this->value);
		}
	}
?>