<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	abstract class RangedPrimitive extends BasePrimitive
	{
		protected $min = null;
		protected $max = null;
		
		public function getMin()
		{
			return $this->min;
		}
		
		/**
		 * @return RangedPrimitive
		**/
		public function setMin($min)
		{
			$this->min = $min;
			
			return $this;
		}
		
		public function getMax()
		{
			return $this->max;
		}
		
		/**
		 * @return RangedPrimitive
		**/
		public function setMax($max)
		{
			$this->max = $max;
			
			return $this;
		}
	}
?>