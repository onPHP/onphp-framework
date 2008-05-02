<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
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
	 * @ingroup Module
	**/
	abstract class RangedPrimitive extends TypedPrimitive
	{
		protected $min = null;
		protected $max = null;
		
		public function getMin()
		{
			return $this->atom->getMin();
		}
		
		/**
		 * @return RangedPrimitive
		**/
		public function setMin($min)
		{
			$this->atom->setMin($min);
			
			return $this;
		}
		
		public function getMax()
		{
			return $this->atom->getMax();
		}
		
		/**
		 * @return RangedPrimitive
		**/
		public function setMax($max)
		{
			$this->atom->setMax($max);
			
			return $this;
		}
	}
?>