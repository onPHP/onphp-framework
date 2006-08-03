<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Dmitry E. Demidov                          *
 *   Dmitry.Demidov@noussoft.com                                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class Drawer
	{
		private	$turingImage	= null;
		
		public function setTuringImage(TuringImage $turingImage)
		{
			$this->turingImage = $turingImage;
			
			return $this;
		}
		
		public function getTuringImage()
		{
			return $this->turingImage;
		}
	}
?>