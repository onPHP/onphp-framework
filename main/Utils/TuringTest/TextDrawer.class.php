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

	/**
	 * @ingroup Turing
	**/
	abstract class TextDrawer extends Drawer
	{
		const SPACE_RATIO = 10;
		
		private $size = null;
		
		abstract public function draw($text);
		
		public function __construct($size)
		{
			$this->size = $size;
		}
		
		/**
		 * @return TextDrawer
		**/
		public function drawCraracter($angle, $x, $y, $character)
		{
			$color = $this->getTuringImage()->getOneCharacterColor();
			
			imagettftext(
				$this->getTuringImage()->getImageId(),
				$this->size,
				$angle,
				$x,
				$y,
				$color,
				$this->getFont(),
				$character
			);
			
			return $this;
		}
		
		protected function getSize()
		{
			return $this->size;
		}
		
		/**
		 * @return TextDrawer
		**/
		protected function showError()
		{
			$drawer = new ErrorDrawer($this->getTuringImage());
			$drawer->draw();
			
			return $this;
		}

		protected function getTextWidth($string)
		{
			$textWidth = 0;
			
			for ($i = 0, $length = strlen($string); $i < $length; ++$i) {
				$character = $string[$i];
				$textWidth += $this->getStringWidth($character) + $this->getSpace();
			}
			
			return $textWidth;
		}
		
		protected function getStringWidth($string)
		{
			$bounds = imagettfbbox($this->size, 0, $this->getFont(), $string);
			
			return $bounds[2] - $bounds[0];
		}
		
		protected function getStringHeight($string)
		{
			$bounds = imagettfbbox($this->size, 0, $this->getFont(), $string);
			
			return $bounds[1] - $bounds[7];
		}
		
		protected function getMaxCharacterHeight()
		{
			return $this->getStringHeight('W'); // bigest character
		}
		
		protected function getSpace()
		{
			return $this->getSize() / TextDrawer::SPACE_RATIO;
		}
		
		private function getFont()
		{
			return $this->getTuringImage()->getFont();
		}
	}
?>