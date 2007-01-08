<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Image uploads helper.
	 * 
	 * @ingroup Primitives
	**/
	final class PrimitiveImage extends PrimitiveFile
	{
		private $width		= null;
		private $height		= null;
		
		private $maxWidth	= null;
		private $minWidth	= null;
		
		private $maxHeight	= null;
		private $minHeight	= null;
		
		private $type		= null;
		
		/**
		 * @return PrimitiveImage
		**/
		public function clean()
		{
			$this->width = $this->height = null;
			
			$this->type = null;
			
			return parent::clean();
		}
		
		public function getWidth()
		{
			return $this->width;
		}
		
		public function getHeight()
		{
			return $this->height;
		}
		
		public function getType()
		{
			return $this->type;
		}

		public function setMaxWidth($max)
		{
			$this->maxWidth = $max;
			
			return $this;
		}
		
		public function setMinWidth($min)
		{
			$this->minWidth = $min;
			
			return $this;
		}
		
		public function setMaxHeight($max)
		{
			$this->maxHeight = $max;
			
			return $this;
		}
		
		public function setMinHeight($min)
		{
			$this->minHeight = $min;
			
			return $this;
		}
		
		public function import($scope)
		{
			if (!parent::import($scope))
				return null;
			
			try {
				list($width, $height, $type) = getimagesize($this->value);
			} catch (BaseException $e) {
				$this->value = null;
				return false;
			}
			
			if (
				!($this->maxWidth && ($width > $this->maxWidth))
				&& !($this->minWidth && ($width < $this->minWidth))
				&& !($this->maxHeight && ($height > $this->maxHeight))
				&& !($this->minHeight && ($height < $this->minHeight))
			) {
				$this->type = new ImageType($type);
				$this->width = $width;
				$this->height = $height;
				
				return true;
			}
			
			return false;
		}
	}
?>