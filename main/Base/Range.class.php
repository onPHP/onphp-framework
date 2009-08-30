<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Integer's interval implementation and accompanying utility methods.
	 * 
	 * @ingroup Helpers
	**/
	class Range
	{
		private $min			= null;
		private $max			= null;
		private $swapAllowed	= true;
		
		public function __construct($min = null, $max = null)
		{
			$this->min = $min;
			$this->max = $max;
		}
		
		public function setSwapAllowed()
		{
			$this->swapAllowed = true;
			
			return $this;
		}
		
		public function setSwapDisallowed()
		{
			$this->swapAllowed = false;
			
			return $this;
		}
		
		public function getMin()
		{
			return $this->min;
		}
		
		public function setMin($min = null)
		{
			iF (($this->max) && (int) $min > $this->max && $this->swapAllowed)
				throw new WrongArgumentException(
					'can not set minimal value, which is greater than maximum one'
				);
			else
				$this->min = (int) $min;
			
			return $this;
		}
		
		public function getMax()
		{
			return $this->max;
		}
		
		public function setMax($max = null)
		{
			if (($this->min) && (int) $max < $this->min && $this->swapAllowed)
				throw new WrongArgumentException(
					'can not set maximal value, which is lower than minimum one'
				);
			else
				$this->max = (int) $max;
			
			return $this;
		}

		/// atavism wrt BC
		public function toString($from = 'от', $to = 'до')
		{
			$out = null;
			
			if ($this->min)
				$out .= "{$from} ".$this->min;

			if ($this->max)
				$out .= " {$to} ".$this->max;
				
			return trim($out);
		}
		
		public function divide($factor, $precision = null)
		{
			if ($this->min)
				$this->min = round($this->min / $factor, $precision);

			if ($this->max)
				$this->max = round($this->max / $factor, $precision);
			
			return $this;
		}
		
		public function multiply($multiplier)
		{
			if ($this->min)
				$this->min = $this->min * $multiplier;
			
			if ($this->max)
				$this->max = $this->max * $multiplier;
			
			return $this;
		}

		public function equals(Range $range)
		{
			return ($this->min === $range->getMin() &&
					$this->max === $range->getMax());
		}
		
		public function isEmpty()
		{
			return
				($this->min === null) &&
				($this->max === null);
		}
		
		public static function swap(&$a, &$b)
		{
			$c = $a;
			$a = $b;
			$b = $c;
		}

		public static function buildObject(
			$min = null, $max = null, $swapAllowed = true
		)
		{
			if ($min || $max) {
				$range = new Range();

				if ($swapAllowed)
					$range->setSwapAllowed();
				else
					$range->setSwapDisallowed();

				if (
					isset($min, $max)
					&& ((int) $min > (int) $max)
					&& $swapAllowed
				)
					self::swap($min, $max);
				
				if ($min && ((int) $min))
					$range->setMin($min);
				
				if ($max && ((int) $max))
					$range->setMax($max);

				return $range;
			}

			return null;
		}
	}
?>