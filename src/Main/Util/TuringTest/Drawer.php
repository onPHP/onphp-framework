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

namespace OnPHP\Main\Util\TuringTest;

/**
 * @ingroup Turing
**/
abstract class Drawer
{
	private	$turingImage	= null;

	/**
	 * @return Drawer
	**/
	public function setTuringImage(TuringImage $turingImage)
	{
		$this->turingImage = $turingImage;

		return $this;
	}

	/**
	 * @return TuringImage
	**/
	public function getTuringImage()
	{
		return $this->turingImage;
	}
}
?>