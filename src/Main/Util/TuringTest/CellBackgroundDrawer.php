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
final class CellBackgroundDrawer extends BackgroundDrawer
{
	private $step = null;

	public function __construct($step)
	{
		$this->step = $step;
	}

	/**
	 * @return CellBackgroundDrawer
	**/
	public function draw()
	{
		$x = mt_rand(-$this->step, $this->step);
		$width = $this->getTuringImage()->getWidth();

		while ($x < $width) {
			$color = $this->makeColor();
			$colorId = $this->getTuringImage()->getColorIdentifier($color);

			imageline(
				$this->getTuringImage()->getImageId(),
				$x,
				0,
				$x,
				$this->getTuringImage()->getHeight(),
				$colorId
			);

			$x += $this->step;
		}

		$y = mt_rand(-$this->step, $this->step);
		$height = $this->getTuringImage()->getHeight();

		while ($y < $height) {
			$color = $this->makeColor();
			$colorId = $this->getTuringImage()->getColorIdentifier($color);

			imageline(
				$this->getTuringImage()->getImageId(),
				0,
				$y,
				$this->getTuringImage()->getWidth(),
				$y,
				$colorId
			);

			$y += $this->step;
		}

		return $this;
	}
}
?>