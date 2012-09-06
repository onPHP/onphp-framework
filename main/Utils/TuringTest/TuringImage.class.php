<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Dmitry E. Demidov                          *
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
	final class TuringImage
	{
		private $textColors			= null;
		private $backgroundColors	= null;
		
		private $font		= null;
		
		private $imageId	= null;
		
		private $width		= null;
		private $height		= null;
		
		private $generator	= null;
		
		private $drawer				= null;
		private $backgroundDrawer	= null;
		
		private $code		= null;
		
		public function __construct($width, $height)
		{
			$this->width = $width;
			$this->height = $height;
			
			$this->generator = new CodeGenerator();
			$this->textColors = new ColorArray();
			$this->backgroundColors = new ColorArray();
		}
		
		/**
		 * @return TuringImage
		**/
		public function setGeneratedCode($code)
		{
			$this->code = $code;
			
			return $this;
		}
		
		public function getGeneratedCode()
		{
			if (!$this->code)
				$this->code = $this->generator->generate();
				
			return $this->code;
		}
		
		public function getTextColors()
		{
			return $this->textColors;
		}
		
		public function getBackgroundColors()
		{
			return $this->backgroundColors;
		}
		
		public function getWidth()
		{
			return $this->width;
		}
		
		public function getHeight()
		{
			return $this->height;
		}
		
		public function getImageId()
		{
			return $this->imageId;
		}
		
		public function getFont()
		{
			return $this->font;
		}
		
		public function setFont($font)
		{
			$this->font = $font;
			
			return $this;
		}
		
		/**
		 * @return TuringImage
		**/
		public function setTextDrawer(TextDrawer $drawer)
		{
			$drawer->setTuringImage($this);
			$this->drawer = $drawer;
			
			return $this;
		}
		
		/**
		 * @return TuringImage
		**/
		public function setBackgroundDrawer(BackgroundDrawer $drawer)
		{
			$drawer->setTuringImage($this);
			$this->backgroundDrawer = $drawer;
			
			return $this;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function getCodeGenerator()
		{
			return $this->generator;
		}
		
		public function getColorIdentifier(Color $color)
		{
			$colorId =
				imagecolorexact(
					$this->imageId,
					$color->getRed(),
					$color->getGreen(),
					$color->getBlue()
				);
			
			if ($colorId === -1)
				$colorId =
					imagecolorallocate(
						$this->imageId,
						$color->getRed(),
						$color->getGreen(),
						$color->getBlue()
					);
			
			return $colorId;
		}
		
		public function getOneCharacterColor()
		{
			$textColor=$this->textColors->getRandomTextColor();
			
			return $this->getColorIdentifier($textColor);
		}
		
		/**
		 * @return TuringImage
		**/
		public function toImage(ImageType $imageType)
		{
			if ($this->drawer === null)
				throw new WrongStateException('drawer must present');
			
			$this->init();
			
			$this->drawBackGround();
			
			$this->drawer->draw($this->getGeneratedCode());
			
			$this->outputImage($imageType);
			
			imagedestroy($this->getImageId());
			
			return $this;
		}
		
		/**
		 * @return TuringImage
		**/
		private function init()
		{
			$imageId = imagecreate($this->getWidth(), $this->getHeight());
			$this->imageId = $imageId;
			
			$this->getColorIdentifier(new Color('FFFFFF')); // white background
			
			return $this;
		}
		
		/**
		 * @return TuringImage
		**/
		private function drawBackGround()
		{
			if (!$this->backgroundColors->isEmpty()) {
				$backgroundColor = $this->backgroundColors->getRandomTextColor();
				
				if ($backgroundColor !== null) {
					$backgroundColorId = $this->getColorIdentifier($backgroundColor);
					
					imagefilledrectangle(
						$this->imageId,
						0,
						0,
						$this->getWidth(),
						$this->getHeight(),
						$backgroundColorId
					);
				}
			}
			
			if ($this->backgroundDrawer !== null)
				$this->backgroundDrawer->draw();
			
			return $this;
		}
		
		/**
		 * @return TuringImage
		**/
		private function outputImage(ImageType $imageType)
		{
			$gdImageTypes = imagetypes();
			
			switch ($imageType->getId()) {
				
				case ImageType::WBMP:
					
					if ($gdImageTypes & IMG_WBMP) {
						header("Content-type: image/vnd.wap.wbmp");
						imagewbmp($this->imageId);
						break;
					}
				
				case ImageType::PNG:
					
					if ($gdImageTypes & IMG_PNG) {
						header("Content-type: image/png");
						imagepng($this->imageId);
						break;
					}
				
				case ImageType::JPEG:
				
					if ($gdImageTypes & IMG_JPG) {
						header("Content-type: image/jpeg");
						imagejpeg($this->imageId);
						break;
					}
				
				case ImageType::GIF:
					
					if ($gdImageTypes & IMG_GIF ) {
						header("Content-type: image/gif");
						imagegif($this->imageId);
						break;
					}
				
				default:
					
					throw new UnimplementedFeatureException(
						'requesting non-supported format'
					);
			}
			
			return $this;
		}
	}
?>