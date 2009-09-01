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
	abstract class BaseType
	{
		protected $value = null;
		
		abstract public function setValue($value);
		
		public function __construct($value = null)
		{
			if (null !== $value)
				$this->setValue($value);
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function dropValue()
		{
			$this->value = null;
			
			return $this;
		}
	}
?>