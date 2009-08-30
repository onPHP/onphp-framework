<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class ExplodedPrimitive extends PrimitiveString
	{
		protected $separator 		= ' ';
		protected $splitByRegexp	= false;
		
		/**
		 * @return ExplodedPrimitive
		**/
		public function setSeparator($separator)
		{
			$this->separator = $separator;
			
			return $this;
		}
		
		public function getSeparator()
		{
			return $this->separator;
		}
		
		public function setSplitByRegexp($splitByRegexp = false)
		{
			$this->splitByRegexp = ($splitByRegexp === true);
			
			return $this;
		}
		
		public function isSplitByRegexp()
		{
			return $this->splitByRegexp;
		}
		
		public function import($scope)
		{
			if (!$result = parent::import($scope))
				return $result;
			
			if (
				$this->value =
					$this->isSplitByRegexp()
						?
							preg_split(
								$this->separator,
								$this->value,
								-1,
								PREG_SPLIT_NO_EMPTY
							)
						: explode($this->separator, $this->value)
			) {
				return true;
			} else {
				return false;
			}
			
			Assert::isUnreachable();
		}
	}
?>