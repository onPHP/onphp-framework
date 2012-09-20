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
		const X_COORDINATE = 0;
		const Y_COORDINATE = 1;
		const Z_COORDINATE = 2;
		
		/**
		 * Coordinates
		**/
		private $vector = array();
		
		/**
		 * @return Point
		**/
		public static function create($vector)
		{
			if (is_array($vector))
				return new self($vector);
			elseif (is_string($vector))
				return self::createFromString($vector);
			else
				throw new WrongArgumentException('Strange arguments given');
		}

		/**
		 * @param string $point
		 * 
		 * Expected values:
		 * ( x , y, ... )
		 *   x , y, ...
		 * 
		 * @return Point
		**/
		public static function createFromString($point)
		{
			if (strpos($point, '(') === 0)
				$point = substr($point, 1, -1);
			
			return new self(preg_split('/\s*,\s*/', $point));
		}		
		
		public function __construct(array $vector)
		{
			Assert::isNotEmpty($vector);
			
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
		 * @throws WrongArgumentException 
		 * @return float|int
		**/
		public function getCoordinate($n)
		{
			if (!isset($this->vector[$n])) {
				throw new WrongArgumentException(
					'Invalid coordinate number: '.$n
				);
			}
			
			return $this->vector[$n];
		}
	}
?>