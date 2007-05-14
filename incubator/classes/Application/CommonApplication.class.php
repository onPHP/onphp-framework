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
	
	class CommonApplication extends BaseApplication
	{
		const CSS_PATH			= 'css';
		const IMG_PATH			= 'img';
		const SHARED_CSS_PATH	= 'sharedCss';
		const SHARED_IMG_PATH	= 'sharedImg';
		
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
		public function setImgStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::IMG_PATH, $storage);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setImgStoragePath($path)
		{
			return $this->setImgStorage(
				CommonStaticStorage::create(
					ApplicationUrl::create()->
					setUrl($this->url().$path)
				)->
				setStrict(false)
			);
		}
		
		public function getImgStorage()
		{
			return $this->getStaticStorage(self::IMG_PATH);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setCssStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::CSS_PATH, $storage);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setCssStoragePath($path)
		{
			return $this->setCssStorage(
				CommonStaticStorage::create(
					ApplicationUrl::create()->
					setUrl($this->url().$path)
				)->
				setExtensionsList(array('.css'))
			);
		}
		
		public function getCssStorage()
		{
			return $this->getStaticStorage(self::CSS_PATH);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setCommonImgStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::SHARED_IMG_PATH, $storage);
		}
		
		/**
		 * @return CommonApplication
		**/
		public function setCommonCssStorage(CommonStaticStorage $storage)
		{
			return $this->setStaticStorage(self::SHARED_CSS_PATH, $storage);
		}
		
		public function img($name)
		{
			return $this->getStaticStorage(self::IMG_PATH)->getUrl($name);
		}
		
		public function css($name)
		{
			return $this->getStaticStorage(self::CSS_PATH)->getUrl($name);
		}
		
		public function sharedImg($name)
		{
			return
				$this->getStaticStorage(self::SHARED_IMG_PATH)->
					getUrl($name);
		}
		
		public function sharedCss($name)
		{
			return
				$this->getStaticStorage(self::SHARED_CSS_PATH)->
					getUrl($name);
		}
	}
?>