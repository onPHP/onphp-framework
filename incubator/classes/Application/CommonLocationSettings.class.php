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
/* $Id$ */
	
	class CommonLocationSettings extends LocationSettings
	{
		const WEB	= 'web';
		const WAP	= 'wap';
		const ADMIN	= 'admin';
		const SOAP	= 'soap';
		
		protected $locations = array(
			self::WEB	=> null,
			self::WAP	=> null,
			self::ADMIN	=> null,
			self::SOAP	=> null
		);
		
		/**
		 * @return CommonLocationSettings
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setWeb(ApplicationUrl $webLocation)
		{
			return $this->set(self::WEB, $webLocation);
		}
		
		public function getWeb()
		{
			return $this->get(self::WEB);
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setWebUrl($url)
		{
			return $this->setWeb(ApplicationUrl::create()->setUrl($url));
		}
		
		public function getWebUrl()
		{
			return $this->getWeb()->getUrl();
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setWap(ApplicationUrl $wapLocation)
		{
			return $this->set(self::WAP, $wapLocation);
		}
		
		public function getWap()
		{
			return $this->get(self::WAP);
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setWapUrl($url)
		{
			return $this->setWap(ApplicationUrl::create()->setUrl($url));
		}
		
		public function getWapUrl()
		{
			return $this->getWap()->getUrl();
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setAdmin(ApplicationUrl $adminLocation)
		{
			return $this->set(self::ADMIN, $adminLocation);
		}
		
		public function getAdmin()
		{
			return $this->get(self::ADMIN);
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setAdminUrl($url)
		{
			return $this->setAdmin(ApplicationUrl::create()->setUrl($url));
		}
		
		public function getAdminUrl()
		{
			return $this->getAdmin()->getUrl();
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setSoap(ApplicationUrl $soapLocation)
		{
			return $this->set(self::SOAP, $soapLocation);
		}
		
		public function getSoap()
		{
			return $this->get(self::SOAP);
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function setSoapUrl($url)
		{
			return $this->setSoap(ApplicationUrl::create()->setUrl($url));
		}
		
		public function getSoapUrl()
		{
			return $this->getSoap()->getUrl();
		}
	}
?>