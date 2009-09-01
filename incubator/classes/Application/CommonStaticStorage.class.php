<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	class CommonStaticStorage extends StaticStorage
	{
		private $strict				= true;
		private $extensionsList		= null;
		private $shared				= false;
		
		/**
		 * @return CommonStaticStorage
		**/
		//TODO: Use main/Application/ApplicationUrl
		public static function create(AppUrl $baseUrl)
		{
			return new self($baseUrl);
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function setExtensionsList($extensionsList)
		{
			$this->extensionsList	= $extensionsList;
			
			return $this;
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function setStrict($isStrict)
		{
			Assert::isBoolean($isStrict);
			
			$this->strict = $isStrict;
			
			return $this;
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function setShared($isShared)
		{
			Assert::isBoolean($isShared);
			
			$this->shared = $isShared;
			
			return $this;
		}
		
		public function getUrl($name)
		{
			return
				$this->baseUrl->getUrl()
				.(
					$this->shared
					? Application::me()->getLocationArea().'/'
					: null
				)
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