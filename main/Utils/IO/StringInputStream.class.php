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

	/**
	 * @ingroup Utils
	**/
	final class StringInputStream extends InputStream
	{
		private $string		= null;
		private $length		= null;
		
		private $position	= 0;
		private $mark		= 0;
		
		public function __construct($string)
		{
			Assert::isString($string);
			
			$this->string = $string;
			$this->length = strlen($string);
		}
		
		/**
		 * @return StringInputStream
		**/
		public static function create($string)
		{
			return new self($string);
		}
		
		public function isEof()
		{
			return ($this->position >= $this->length);
		}
		
		/**
		 * @return StringInputStream
		**/
		public function mark()
		{
			$this->mark = $this->position;
			
			return $this;
		}
		
		public function markSupported()
		{
			return true;
		}
		
		/**
		 * @return StringInputStream
		**/
		public function reset()
		{
			$this->position = $this->mark;
			
			return $this;
		}
		
		/**
		 * @return StringInputStream
		**/
		public function close()
		{
			$this->string = null;
			
			return $this;
		}
		
		public function read($count)
		{
			if (!$this->string || $this->isEof())
				return null;
			
			if ($count == 1) {
				$result = $this->string[$this->position];
			} else {
				$result = substr($this->string, $this->position, $count);
			}
			
			$this->position += $count;
			
			return $result;
		}
		
		public function getPosition()
		{
			return $this->position;
		}
	}
?>