<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
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
	final class PrimitiveEnumerationList extends PrimitiveEnumeration
	{
		protected $value = array();
		
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
		
		public function import($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifierList '{$name}'"
				);
			
			if (!BasePrimitive::import($scope, $prefix))
				return null;
			
			$name = $this->getActualName($prefix);
			
			if (!is_array($scope[$name]))
				return false;
			
			$list = array_unique($scope[$name]);
			
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