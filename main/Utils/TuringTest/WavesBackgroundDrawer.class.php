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
	final class WavesBackgroundDrawer extends BackgroundDrawer
	{
		const MIN_WAVE_DISTANCE	= 8;
		const MAX_WAVE_DISTANCE	= 20;
		const MAX_WAVE_OFFSET	= 5;
		
		/**
		 * @return WavesBackgroundDrawer
		**/
		public function draw()
		{
			$y = mt_rand(-self::MAX_WAVE_OFFSET, self::MAX_WAVE_OFFSET);
			
			while ($y < $this->getTuringImage()->getHeight()) {
				$this->drawWave($y);
				
				$y += mt_rand(self::MIN_WAVE_DISTANCE, self::MAX_WAVE_DISTANCE);
			}
			
			return $this;
		}
		
		/* void */ private function drawWave($y)
		{
			$radius = 5;
			$frequency = 30;
			
			$imageId = $this->getTuringImage()->getImageId();
			
			for (
				$x = 0, $width = $this->getTuringImage()->getWidth();
				$x < $width;
				++$x
			) {
				$color = $this->makeColor();
				$colorId = $this->getTuringImage()->getColorIdentifier($color);
				
				$angle = $x % $frequency;
				$angle = 2 * M_PI * $angle / $frequency;
				
				$dy = $radius * sin($angle);
				
				imagesetpixel(
					$imageId,
					$x,
					$y + $dy,
					$colorId
				);
			}
		}
	}
?>