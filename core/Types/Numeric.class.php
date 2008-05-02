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
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	abstract class Numeric extends RangedType
	{
		abstract protected function checkValue($value);
		abstract protected function castValue($value);
		
		/**
		 * @return Numeric
		**/
		public function set($value)
		{
			if ($this->checkValue($value)) {
				$this->checkLimits($value);
				
				$this->value = $this->castValue($value);
				
				return $this;
			}
			
			throw new WrongArgumentException();
		}
	}
?>