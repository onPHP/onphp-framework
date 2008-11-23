<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Igor V. Gulyaev                            *
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
	class PrimitiveDateRange extends RangedPrimitive
	{
		private $className = null;
		
		/**
		 * @return PrimitiveDateRange
		**/
		public static function create($name)
		{
			return new self($name);
		}
		
		public function getTypeName()
		{
			return 'DateRange';
		}
		
		public function isObjectType()
		{
			return true;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDateRange
		**/
		public function of($class)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($class, $this->getObjectName())
			);
			
			$this->className = $class;
			
			return $this;
		}
		
		public function importValue($value)
		{
			try {
				if ($value) {
					$this->checkType($value);
					
					if ($this->checkRanges($value)) {
						$this->value = $value;
						return true;
					} else {
						return false;
					}
				} else {
					return BasePrimitive::importValue(null);
				}
			} catch (WrongArgumentException $e) {
				return false;
			}
		}
		
		public function import(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			try {
				$range = $this->makeRange($scope[$this->name]);
			} catch (WrongArgumentException $e) {
				return false;
			}
			
			if ($this->checkRanges($range)) {
				if (
					$this->className
					&& ($this->className != $this->getObjectName())
				) {
					$newRange = new $this->className;
					
					if ($start = $range->getStart())
						$newRange->setStart($start);
					
					if ($end = $range->getEnd())
						$newRange->setEnd($end);
					
					$this->value = $newRange;
					return true;
				}
				
				$this->value = $range;
				return true;
			}
			
			return false;
		}
		
		protected function getObjectName()
		{
			return 'DateRange';
		}
		
		protected function checkRanges(DateRange $range)
		{
			return
				!($this->min && ($this->min->toStamp() < $range->getStartStamp()))
				&& !($this->max && ($this->max->toStamp() > $range->getEndStamp()));
		}
		
		protected function makeRange($string)
		{
			return DateRangeList::makeRange($string);
		}
		
		/* void */ private function checkType($object)
		{
			if ($this->className)
				Assert::isTrue(
					ClassUtils::isInstanceOf($object, $this->className)
				);
			else
				Assert::isTrue(
					ClassUtils::isInstanceOf($object, $this->getObjectName())
				);
		}
	}
?>