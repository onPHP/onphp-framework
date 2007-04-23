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

	class LocationSettings
	{
		const WEB	= 'web';
		const WAP	= 'wap';
		const ADMIN	= 'admin';
		const SOAP	= 'soap';

		private $locations = array(
			self::WEB	=> null,
			self::WAP	=> null,
			self::ADMIN	=> null,
			self::SOAP	=> null
		);
		
		/**
		 * @return LocationSettings
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return LocationSettings
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
		 * @return LocationSettings
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
		 * @return LocationSettings
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
		 * @return LocationSettings
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
		 * @return LocationSettings
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
		 * @return LocationSettings
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
		 * @return LocationSettings
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
		 * @return LocationSettings
		**/
		public function setSoapUrl($url)
		{
			return $this->setSoap(ApplicationUrl::create()->setUrl($url));
		}

		public function getSoapUrl()
		{
			return $this->getSoap()->getUrl();
		}
		
		/**
		 * @return LocationSettings
		**/
		public function set($area, ApplicationUrl $location)
		{
			$this->locations[$area] = $location;

			return $this;
		}

		public function get($area)
		{
			if (!isset($this->locations[$area]))
				throw
					new WrongArgumentException(
						"location {{$area}} does not defined"
					);
			
			return $this->locations[$area];
		}
	}
?>