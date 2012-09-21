<?php
/***************************************************************************
 *   Copyright (C) 2012 by Nikita V. Konstantinov                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Geometry
	**/
	final class Point implements Stringable, DialectString
	{
		const X = 0;
		const Y = 1;
		const Z = 2;
		
		/**
		 * Coordinates
		**/
		private $vector = array();
		
		/**
		 * @return Point
		**/
		public static function create(/* ... */)
		{
			if (func_num_args() == 1)
				return new self(func_get_arg(0));
			
			return new self(func_get_args());
		}

		public function __construct(/* ... */)
		{
			$vector = func_get_arg(0);
			
			if (!is_array($vector)) {
				if (is_string($vector))
					$vector = $this->getVectorByString($vector);
				else
					$vector = func_get_args();
			}

			Assert::isNotEmptyArray($vector);
			
			$this->vector = $vector;
		}
		
		public function toString()
		{
			return '('.implode(', ', $this->vector).')';
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteValue($this->toString());
		}
		
		/**
		 * @return int
		**/		
		public function getNumberOfCoordinates()
		{
			return count($this->vector);
		}

		/**
		 * @return bool
		**/
		public function belongsToPlane()
		{
			return count($this->vector) == 2;
		}
		
		/**
		 * @return bool
		**/
		public function isEqual(Point $point)
		{
			if (count($this->vector) != count($point->vector))
				return false;
			
			for ($i = 0; $i < count($this->vector); $i++)
				if ($this->vector[$i] != $point->vector[$i])
					return false;
				
			return true;
		}
	
		/**
		 * @param int $n
		 * @param int|float $value
		 * @return Point
		 * @throws WrongArgumentException 
		**/
		public function setCoordinate($n, $value)
		{
			if (!isset($this->vector[$n])) {
				throw new WrongArgumentException(
					'You cannot set coordinate #'.$n.' for this point'
				);
			}
			
			$this->vector[$n] = $value;
			
			return $this;			
		}
		
		/**
		 * @return Point 
		 */
		public function setX($value)
		{
			$this->setCoordinate(self::X, $value);
			return $this;
		}
		
		/**
		 * @return Point 
		 */
		public function setY($value)
		{
			$this->setCoordinate(self::Y, $value);
			return $this;
		}
		
		/**
		 * @return Point 
		 */
		public function setZ($value)
		{
			$this->setCoordinate(self::Z, $value);
			return $this;
		}
		
		/**
		 * @param int $n
		 * @throws WrongArgumentException 
		 * @return float|int
		**/
		public function getCoordinate($n)
		{
			if (!isset($this->vector[$n])) {
				throw new WrongArgumentException(
					'There is no coordinate #'.$n
				);
			}
			
			return $this->vector[$n];
		}
		
		public function getX()
		{
			return $this->getCoordinate(self::X);
		}
		
		public function getY()
		{
			return $this->getCoordinate(self::Y);
		}
		
		public function getZ()
		{
			return $this->getCoordinate(self::Z);
		}
		
		/**
		 * @param string $point
		 * 
		 * Expected values:
		 * ( x , y, ... )
		 *   x , y, ...
		 * 
		 * @return array
		**/
		private function getVectorByString($point)
		{			
			if (strpos($point, '(') === 0)
				$point = substr($point, 1, -1);
			
			return preg_split('/\s*,\s*/', $point);
		}		
	}
?>