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
	
	class CommonApplication extends BaseApplication
	{
		const CSS_STORAGE			= 1;
		const IMG_STORAGE			= 2;
		const SHARED_CSS_STORAGE	= 3;
		const SHARED_IMG_STORAGE	= 4;
		
		/**
		 * @return CommonApplication
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setLocations(LocationSettings $locations)
		{
			Assert::isTrue($locations instanceof CommonLocationSettings);
			
			return parent::setLocations($locations);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function resideInWeb()
		{
			return $this->reside(CommonLocationSettings::WEB);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function resideInWap()
		{
			return $this->reside(CommonLocationSettings::WAP);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function resideInAdmin()
		{
			return $this->reside(CommonLocationSettings::ADMIN);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function resideInSoap()
		{
			return $this->reside(CommonLocationSettings::SOAP);
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function getWebLocation()
		{
			return $this->locations->getWeb();
		}
		
		public function getWebUrl()
		{
			return $this->getWebLocation()->getUrl();
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function getWapLocation()
		{
			return $this->locations->getWap();
		}
		
		public function getWapUrl()
		{
			return $this->getWapLocation()->getUrl();
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function getAdminLocation()
		{
			return $this->locations->getAdmin();
		}
		
		public function getAdminUrl()
		{
			return $this->getAdminLocation()->getUrl();
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function getSoapLocation()
		{
			return $this->locations->getSoap();
		}
		
		public function getSoapUrl()
		{
			return $this->getSoapLocation()->getUrl();
		}
		
		/**
		 * @return CommonApplication
		**/
		// TODO: make dispatcher?
		public function navigate($requestUri)
		{
			Assert::isNotNull($this->location);
			Assert::isNotNull($this->location->getNavigationSchema());
			
			if (strpos($requestUri, $this->location->getPath()) === false)
				throw new WrongArgumentException(
					'location settings is broken?'
				);
			
			$this->setNavigationArea(
				$this->location->getNavigationSchema()->getArea(
					substr($requestUri, strlen($this->location->getPath()))
				)
			);
			
			return $this;
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setImgStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::IMG_STORAGE, $storage);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setImgStoragePath($path)
		{
			return $this->setImgStorage(
				CommonStaticStorage::create(
					//TODO: Use main/Application/ApplicationUrl
					AppUrl::create()->
					setUrl($this->baseUrl().$path)
				)->
				setStrict(false)
			);
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function getImgStorage()
		{
			return $this->getStaticStorage(self::IMG_STORAGE);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setCssStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::CSS_STORAGE, $storage);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setCssStoragePath($path)
		{
			return $this->setCssStorage(
				CommonStaticStorage::create(
					//TODO: Use main/Application/ApplicationUrl
					AppUrl::create()->
					setUrl($this->baseUrl().$path)
				)->
				setExtensionsList(array('.css'))
			);
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function getCssStorage()
		{
			return $this->getStaticStorage(self::CSS_STORAGE);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setSharedImgStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::SHARED_IMG_STORAGE, $storage);
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function getSharedImgStorage()
		{
			return $this->getStaticStorage(self::SHARED_IMG_STORAGE);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setSharedCssStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::SHARED_CSS_STORAGE, $storage);
		}
		
		/**
		 * @return CommonStaticStorage
		**/
		public function getSharedCssStorage()
		{
			return $this->getStaticStorage(self::SHARED_CSS_STORAGE);
		}
		
		public function img($name)
		{
			return $this->getStaticStorage(self::IMG_STORAGE)->getUrl($name);
		}
		
		public function css($name)
		{
			return $this->getStaticStorage(self::CSS_STORAGE)->getUrl($name);
		}
		
		public function sharedImg($name)
		{
			return
				$this->getStaticStorage(self::SHARED_IMG_STORAGE)->
					getUrl($name);
		}
		
		public function sharedCss($name)
		{
			return
				$this->getStaticStorage(self::SHARED_CSS_STORAGE)->
					getUrl($name);
		}
	}
?>