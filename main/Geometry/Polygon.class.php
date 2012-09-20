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
	final class Polygon implements Stringable, DialectString
	{
		/**
		 * @var Point[] 
		**/
		private $vertexList = array();
		
		/**
		 * @return Polygon
		**/
		public static function create($vertexList = array())
		{
			if (is_array($vertexList))
				return new self($vertexList);
			elseif (is_string($vertexList))
				return self::createFromString($vertexList);
			else
				throw new WrongArgumentException('Strange arguments given');
		}

		/**
		 * @param string $polygon
		 * 
		 * Expected values (according to Postgres format):
		 * ( ( x1 , y1 ) , ... , ( xn , yn ) )
		 *   ( x1 , y1 ) , ... , ( xn , yn )  
		 *   ( x1 , y1   , ... ,   xn , yn )  
		 *     x1 , y1   , ... ,   xn , yn   
		 * 
		 * @return Polygon
		 */
		public static function createFromString($polygon)
		{
			$polygon =
				str_replace(array('(', ')', ' '), '', $polygon);
			
			$coordinateList = explode(',', $polygon);
			
			if (count($coordinateList) % 2 !== 0) {
				throw new WrongArgumentException(
					'Strange list of points given'
				);
			}
				
			$vertexList = array();
			
			for ($i = 0; $i < count($coordinateList); $i += 2) {
				$vertexList[] =
					Point::create(
						array(
							$coordinateList[$i],
							$coordinateList[$i + 1],
						)
					);
			}
			
			return new self($vertexList);
		}
		
		public function __construct(array $vertexList)
		{
			$this->vertexList = $vertexList;
			
			$this->normalizeVertextList();			
		}
		
		public function toString()
		{
			return
				implode(
					', ', 
					array_map(
						function(Point $vertex) {
							return $vertex->toString();
						},
						$this->vertexList
					)				
				);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteValue($this->toString());
		}
		
		/**
		 * @return Polygon 
		**/
		public function addVertex(Point $point)
		{
			Assert::isTrue(
				$point->belongsToPlane(),
				'Passed point cannot be a vertex of 2-dimensional shape'
			);
			
			$this->vertexList[] = $point;
			
			$this->normalizeVertextList();
			
			return $this;
		}
		
		/**
		 * NOTE: this method doesn't check equality of shapes;
		 * also, it leaves transposition of vertices out of account
		 * 
		 * @return bool 
		**/
		public function isEqual(Polygon $polygon)
		{
			if (count($this->vertexList) != count($polygon->vertexList))
				return false;
			
			for ($i = 0; $i < count($this->vertexList); $i++)
				if ($this->vertexList[$i] != $polygon->vertexList[$i])
					return false;
				
			return true;
		}
		
		/**
		 * @return Polygon 
		 */
		public function getBoundingBox()
		{
			Assert::isNotEmpty($this->vertexList);
			
			$vertex = reset($this->vertexList);
						
			$x1 = $vertex->getCoordinate(Point::X_COORDINATE);
			$y1 = $vertex->getCoordinate(Point::Y_COORDINATE);

			$x2 = $vertex->getCoordinate(Point::X_COORDINATE);
			$y2 = $vertex->getCoordinate(Point::Y_COORDINATE);
			
			foreach ($this->vertexList as $vertex) {
				// left bottom				
				$x1 = min($vertex->getCoordinate(Point::X_COORDINATE), $x1);
				$y1 = min($vertex->getCoordinate(Point::Y_COORDINATE), $y1);
				
				// right top
				$x2 = max($vertex->getCoordinate(Point::X_COORDINATE), $x2);
				$y2 = max($vertex->getCoordinate(Point::Y_COORDINATE), $y2);				
			}
			
			return 
				self::create(
					array(
						array($x1, $y1),
						array($x1, $y2),
						array($x2, $y2),
						array($x2, $y1),
					)
				);
		}
		
		private function normalizeVertextList()
		{
			$this->vertexList =
				array_map(
					function($vertex) {
						if ($vertex instanceof Point)
							return $vertex;

						return Point::create($vertex);
					},
					$this->vertexList
				);
					
			return $this;
		}
	}
?>