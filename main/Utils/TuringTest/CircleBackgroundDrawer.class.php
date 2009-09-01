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
	final class CircleBackgroundDrawer extends BackgroundDrawer
	{
		const VERTEX_COUNT	= 20;
		
		private $minRadius	= null;
		private $maxRadius	= null;
		private $count		= null;
	
		public function __construct($count, $minRadius, $maxRadius = null)
		{
			if ($maxRadius === null)
				$maxRadius = $minRadius;
			
			$this->maxRadius = $maxRadius;
			$this->minRadius = $minRadius;
			$this->count = $count;
		}
		
		/**
		 * @return CircleBackgroundDrawer
		**/
		public function draw()
		{
			for ($i = 0; $i < $this->count; ++$i) {
				$y = mt_rand(0, $this->getTuringImage()->getHeight());
				$x = mt_rand(0, $this->getTuringImage()->getWidth());
				
				$radius = mt_rand($this->minRadius, $this->maxRadius);
			
				$this->drawCircle($x, $y, $radius);
			}
			
			return $this;
		}

		/* void */ private function drawCircle($x, $y, $radius)
		{
			$vertexArray = array();
			
			$angleStep = 360 / CircleBackgroundDrawer::VERTEX_COUNT;
			$angle = 0;
			
			for ($i = 0; $i < CircleBackgroundDrawer::VERTEX_COUNT; ++$i) {
				$color = $this->makeColor();
				$colorId = $this->getTuringImage()->getColorIdentifier($color);
				
				$angleRad = deg2rad($angle);
				
				$dx = sin($angleRad) * $radius;
				$dy = cos($angleRad) * $radius;
				
				$vertexArray[] = $x + $dx;
				$vertexArray[] = $y + $dy;
				
				$angle += $angleStep;
			}
			
			imagefilledpolygon(
				$this->getTuringImage()->getImageId(),
				$vertexArray,
				CircleBackgroundDrawer::VERTEX_COUNT,
				$colorId
			);
		}
	}
?>