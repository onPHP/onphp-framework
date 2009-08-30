<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	**/
	abstract class RangedType extends BaseType
	{
		protected $min = null;
		protected $max = null;
		
		/**
		 * @return RangedType
		**/
		public function setMin($min)
		{
			Assert::isInteger($min);
			
			if (null !== $this->max)
				Assert::isGreater($this->max, $min);
			
			$this->min = $min;
			
			return $this;
		}
		
		public function getMin()
		{
			return $this->min;
		}
		
		/**
		 * @return RangedType
		**/
		public function setMax($max)
		{
			Assert::isInteger($max);
			
			if (null !== $this->min)
				Assert::isGreater($max, $this->min);
			
			$this->max = $max;
			
			return $this;
		}
		
		public function getMax()
		{
			return $this->max;
		}
		
		/* void */ protected function checkLimits($value)
		{
			if (
				(
					(null !== ($min = $this->getMin()))
					&& ($value < $min)
				) || (
					(null !== ($max = $this->getMax()))
					&& ($value > $max)
				)
			) {
				throw new OutOfRangeException(
					Assert::dumpArgument($value).' exceeds defined range: '
					.Assert::dumpArgument($min)
					.' - '
					// can be undefined
					.Assert::dumpArgument($this->getMax())
				);
			}
		}
	}
?>