<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	namespace Onphp;

	final class SortHelper extends Singleton implements Instantiatable
	{
		const ASC 	= 0x1;
		const DESC 	= 0x2;
		
		private $vector = null;
		private $keys 	= null; // pairs of key name and direction
		
		private $defaultCmpFunction = 'strnatcmp';
		
		public static function me()
		{
			return Singleton::getInstance('\Onphp\SortHelper');
		}
		
		public function setVector(&$vector)
		{
			$this->vector = &$vector;
			
			return $this;
		}
		
		public function setKeys($keys)
		{
			$this->keys = $keys;
			
			foreach ($this->keys as &$keyData)
				if (!isset($keyData[2]))
					$keyData[2] = $this->defaultCmpFunction;
			
			return $this;
		}
		
		public function sort()
		{
			Assert::isGreater(count($this->keys), 0);
			Assert::isNotEmptyArray($this->vector);
			
			usort($this->vector, array($this, "compare"));
		}
		
		private function compare($one, $two, $keyIndex = 0)
		{
			Assert::isTrue(
				isset($one[$this->keys[$keyIndex][0]])
				|| array_key_exists($this->keys[$keyIndex][0], $one),
				'Key must be exist in vector!'
			);
			
			$result =
				$this->keys[$keyIndex][2](
					$one[$this->keys[$keyIndex][0]],
					$two[$this->keys[$keyIndex][0]]
				);
			
			if ($this->keys[$keyIndex][1] == self::DESC)
				$result *= -1;
			
			if ($result == 0) {
				$keyIndex++;
				
				if (isset($this->keys[$keyIndex]))
					$result = $this->compare($one, $two, $keyIndex);
			 }
			
			 return $result;
		}
	}
?>