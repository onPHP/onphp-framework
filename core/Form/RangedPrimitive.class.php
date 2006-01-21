<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	abstract class RangedPrimitive extends BasePrimitive
	{
		protected $min		= null;
		protected $max		= null;
		
		public function getMin()
		{
			return $this->min;
		}
		
		public function setMin($min)
		{
			$this->min = $min;
			
			return $this;
		}
		
		public function getMax()
		{
			return $this->max;
		}
		
		public function setMax($max)
		{
			$this->max = $max;
			
			return $this;
		}
	}
?>