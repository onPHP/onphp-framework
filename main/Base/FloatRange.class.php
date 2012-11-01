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

	namespace Onphp;

	final class FloatRange extends BaseRange
	{
		public function __construct($min = null, $max = null)
		{
			if ($min !== null)
				Assert::isFloat($min);
			
			if ($max !== null)
				Assert::isFloat($max);
			
			parent::__construct($min, $max);
		}
		
		/**
		 * @return \Onphp\FloatRange
		**/
		public static function create($min = null, $max = null)
		{
			return new self($min, $max);
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\FloatRange
		**/
		public function setMin($min = null)
		{
			if ($min !== null)
				Assert::isFloat($min);
			else
				return $this;
			
			return parent::setMin($min);
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\FloatRange
		**/
		public function setMax($max = null)
		{
			if ($max !== null)
				Assert::isFloat($max);
			else
				return $this;
			
			return parent::setMax($max);
		}
	}
?>