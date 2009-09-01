<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

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

		/**
		 * @return PrimitiveImage
		**/
		public function setMaxWidth($max)
		{
			$this->maxWidth = $max;
			
			return $this;
		}
		
		public function getMaxWidth()
		{
			return $this->maxWidth;
		}
		
		/**
		 * @return PrimitiveImage
		**/
		public function setMinWidth($min)
		{
			$this->minWidth = $min;
			
			return $this;
		}
		
		public function getMinWidth()
		{
			return $this->minWidth;
		}
		
		/**
		 * @return PrimitiveImage
		**/
		public function setMaxHeight($max)
		{
			$this->maxHeight = $max;
			
			return $this;
		}
		
		public function getMaxHeight()
		{
			return $this->maxHeight;
		}
		
		/**
		 * @return PrimitiveImage
		**/
		public function setMinHeight($min)
		{
			$this->minHeight = $min;
			
			return $this;
		}
		
		public function getMinHeight()
		{
			return $this->minHeight;
		}
		
		public function import(array $scope)
		{
			if (!$result = parent::import($scope))
				return $result;
			
			try {
				list($width, $height, $type) = getimagesize($this->value);
			} catch (BaseException $e) {
				// bad luck
				return false;
			}
			
			if (!$width || !$height || !$type) {
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