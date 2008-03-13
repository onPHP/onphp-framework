<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Hint: use raw values like 'City_42' or 'Country_42' where City and
	 * Country are childrens of base class GeoLocation, for example.
	 */
	final class PrimitivePolymorphicIdentifier extends PrimitiveIdentifier
	{
		const WRONG_CID_FORMAT	= 201;
		const WRONG_CLASS		= 202;
		
		const DELIMITER			= '.';
		
		private $baseClassName	= null;
		
		public static function export($value)
		{
			Assert::isInstance($value, 'Identifiable');
			
			return get_class($value).self::DELIMITER.$value->getId();
		}
			
		/**
		 * @throws WrongStateException
		**/
		public function of($class)
		{
			throw new WrongStateException(
				'of() must not be called directly, use ofBase()'
			);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitivePolymorphicIdentifier
		**/
		public function ofBase($className)
		{
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			Assert::isTrue(
				ClassUtils::isInstanceof($className, 'DAOConnected'),
				"class '{$className}' must implement DAOConnected interface"
			);
			
			$this->baseClassName = $className;
			
			return $this;
		}
		
		public function getBaseClassName()
		{
			return $this->baseClassName;
		}
		
		public function exportValue()
		{
			if ($this->value === null)
				return null;
			
			return self::export($this->value);
		}
		
		public function importValue($value)
		{
			return $this->import(
				array(
					$this->getName() => self::export($value)
				)
			);
		}
		
		public function import($scope)
		{
			if (isset($scope[$this->name]) && $scope[$this->name]) {
				$this->raw = $scope[$this->name];
				
				try {
					
					list($class, $id) = explode(self::DELIMITER, $this->raw, 2);
					
				} catch (BaseException $e) {
					
					$this->customError = self::WRONG_CID_FORMAT;
					
					return false;
				}
				
				if (!ClassUtils::isInstanceOf($class, $this->baseClassName)) {
					
					$this->customError = self::WRONG_CLASS;
					
					return false;
				}
				
				parent::of($class);
				
				$scope[$this->name] = $id;
			}
			
			return parent::import($scope);
		}
	}
?>