<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class CommonStaticStorage extends StaticStorage
	{
		private $strict				= true;
		private $extensionsList		= null;
		
		public static function create(ApplicationUrl $baseUrl)
		{
			return new self($baseUrl);
		}

		public function setExtensionsList($extensionsList)
		{
			$this->extensionsList	= $extensionsList;
			
			return $this;
		}
		
		public function setStrict($isStrict)
		{
			Assert::isBoolean($isStrict);
			
			$this->strict = $isStrict;
			
			return $this;
		}
		
		public function getUrl($name)
		{
			return
				$this->baseUrl->getUrl()
				.Application::me()->getMarkup()->getCommonName().'/'
				.$this->guessName($name);
		}
		
		protected function guessName($name)
		{
			if (($dotPosition = strrpos($name, '.')) !== false) {
				$baseName = substr($name, 0, $dotPosition);
				$extension = substr($name, $dotPosition);
			} else {
				$baseName = $name;
				$extension = null;
			}
			
			if ($extension) {
				if (
					$this->strict
					&& !in_array($extension, $this->extensionsList)
				)
					throw new WrongArgumentException(
						"extension '{$extension}' is not allowed "
						."for storage {$this->baseUrl->getUrl()}"
					);
					
			} else {
				if ($this->extensionsList) {
					// TODO: guess file name for extension?
					$extension = reset($this->extensionsList);
				}
			}
			
			return $baseName.$extension;
		}
	}
?>