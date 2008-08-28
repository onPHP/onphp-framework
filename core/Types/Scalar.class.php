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
	final class Scalar extends RangedType
	{
		/**
		 * @return Scalar
		**/
		public static function create($value = null)
		{
			return new self($value);
		}
		
		/**
		 * @return Scalar
		**/
		public function setValue($value)
		{
			if (is_scalar($value)) {
				$this->checkLimits(mb_strlen($value));
				
				if (Assert::checkInteger($value))
					$this->value = (int) $value;
				elseif (Assert::checkFloat($value))
					$this->value = (float) $value;
				else
					$this->value = $value;
				
				return $this;
			}
			
			throw new WrongArgumentException();
		}
	}
?>