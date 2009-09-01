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
	
	abstract class Hierarchy
	{
		const VALUE_BASED	= 1;
		const LEVEL_BASED	= 2;
		
		protected $dimension	= null;
		
		// unimplemented:
		private $defaultedDimension			= null;
		private $cubeDimensionAssociations	= array();
		
		public function __construct(Dimension $dimension)
		{
			$this->dimension = $dimension;
		}
		
		/**
		 * @return Hierarchy
		**/
		public static function createByType($type, Dimension $dimension)
		{
			switch ($type) {
				case self::VALUE_BASED:
					return new ValueBasedHierarchy($dimension);
				
				case self::LEVEL_BASED:
					return new LevelBasedHierarchy($dimension);
				
				default:
					throw new WrongArgumentException(
						"unsuported type id == '{$type}'"
					);
			}
			
			Assert::isUnreachable();
		}
	}
?>