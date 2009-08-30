<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Turing
	**/
	class LinearStringDrawer extends TextDrawer
	{
		public function draw($string)
		{
			$maxHeight = $this->getMaxCharacterHeight();
			$y = round($this->getTuringImage()->getHeight() / 2 + $maxHeight / 2);
	
			$textWidth = $this->getTextWidth($string);
			
			if ($this->getTuringImage()->getWidth() > $textWidth) {
				$x = round(($this->getTuringImage()->getWidth() - $textWidth) / 2);
				$angle = 0;
				
				for ($i = 0; $i < strlen($string); $i++) {
					$character = $string[$i];
					$this->drawCraracter($angle, $x, $y, $character);
					$x += $this->getStringWidth($character) + $this->getSpace();
				}
			} else
				$this->showError();
		}
	}
?>