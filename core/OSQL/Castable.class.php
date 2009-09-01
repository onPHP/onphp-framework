<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Cast-able SQL parts.
	 * 
	 * @ingroup OSQL
	 * @ingroup Module
	**/
	abstract class Castable implements DialectString
	{
		protected $cast	= null;
		
		/**
		 * @return Castable
		**/
		public function castTo($cast)
		{
			$this->cast = $cast;
			
			return $this;
		}
	}
?>