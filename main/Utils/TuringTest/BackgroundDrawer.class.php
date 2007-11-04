<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Turing
	**/
	abstract class BackgroundDrawer extends Drawer
	{
		abstract public function draw();
		
		/**
		 * @return Color
		**/
		public function makeColor()
		{
			$color = $this->getTuringImage()->getTextColors()->getRandomTextColor();
			
			$invertColor = clone $color;
			$invertColor->invertColor();
			
			return $invertColor;
		}
	}
?>