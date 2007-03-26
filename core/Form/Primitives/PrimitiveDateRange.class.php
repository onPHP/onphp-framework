<?php
/***************************************************************************
 *   Copyright (C) 2007 by Igor V. Gulyaev                                 *
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
	class PrimitiveDateRange extends FiltrablePrimitive
	{
		private $className = null;
		private $info = null;
		
		public function __construct($name)
		{
			parent::__construct($name);
			
			$this->info = new ReflectionClass($this->getObjectName());
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDateRange
		**/
		public function of($class)
		{
			if (is_string($class))
				$className = $class;
			elseif (is_object($class)) {
				$className = get_class($class);
			}
			
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$info = new ReflectionClass($this->getObjectName());
			
			$testObject = new $className;

			Assert::isTrue(
				is_subclass_of($testObject, $this->getObjectName()) || $info->isInstance($testObject),
				"'{$className}' not heir of '{$this->getObjectName()}'"
			);
			
			$this->className = $className;
			$this->info = new ReflectionClass($className);;
			
			return $this;
		}
		
		/**
		 * @return PrimitiveDateRange
		**/
		public static function create ($name)
		{
			return new self($name);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDateRange
		**/
		public function setDefault(/* DateRange */ $object)
		{
			$this->checkType($object);
			
			$this->default = $object;
			
			return $this;
		}
		
		protected function checkRanges(DateRange $range)
		{
			return
				!($this->min && ($this->min->toStamp() < $range->getStartStamp()))
				&& !($this->max && ($this->max->toStamp() > $range->getEndStamp()));
		}
		
		protected function getObjectName()
		{
			return 'DateRange';
		}
		
		public function importValue($value)
		{
			try {
				if ($value) {
					$this->checkType($value);
					if ($this->checkRanges($value)) {
						$this->value = $value;
						return true;
					}
				} else {
					return parent::importValue(null);
				}
			} catch (WrongArgumentException $e) {
				return false;
			}
		}
		
		public function import($scope)
		{
			if (parent::import($scope)) {
				try {
					$range = DateRangeList::makeRange($scope[$this->name]);
				
					if ($this->checkRanges($range)) {
						if ($this->className) {
							$newRange = new $this->className;
							//TODO: move to makeRange()
							if ($range->getStart())
								$newRange->setStart($range->getStart());
							if ($range->getEnd())
								$newRange->setEnd($range->getEnd());
							$this->value = $newRange;
							return true;
						}
						
						$this->value = $range;
						return true;
					}
				} catch (WrongArgumentException $e) {
					return false;
				}
			}
			
			return false;
		}
		
		/* void */ protected function checkType($object)
		{
			Assert::isTrue(
				is_subclass_of(
					$object,
					$this->className
						? $this->className 
						: $this->getObjectName()
				)
				|| ($this->info && $this->info->isInstance($object))
			);
		}
	}
?>