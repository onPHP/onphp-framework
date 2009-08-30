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
	
	class SessionServer extends Singleton implements Instantiatable
	{
		private $url		= null;
		
		private $locations	= null;
		private $timeout	= null;
		
		private $actionPages = array();
		
		/**
		 * @return SessionServer
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return SessionServer
		**/
		public function setLocations(CommonLocationSettings $locations)
		{
			$this->locations = $locations;
			
			return $this;
		}
		
		/**
		 * @return CommonLocationSettings
		**/
		public function getLocationSettings()
		{
			return $this->locations;
		}
		
		/**
		 * @return SessionServer
		**/
		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;
			
			return $this;
		}
		
		public function getTimeout()
		{
			return $this->timeout;
		}
		
		/**
		 * @return SessionServer
		**/
		public function setUrl($url)
		{
			$this->url = $url;
			
			return $this;
		}
		
		/**
		 * @return SessionServer
		**/
		public function getUrl()
		{
			return $this->url;
		}
		
		public function getRegistrationUrl()
		{
			return $this->getPageUrl(SessionServerUrlSettings::REGISTRATION);
		}
		
		public function getProfileUrl()
		{
			return $this->getPageUrl(SessionServerUrlSettings::PROFILE);
		}
		
		public function getLoginUrl()
		{
			return $this->getPageUrl(SessionServerUrlSettings::LOGIN);
		}
		
		public function getLogoutUrl()
		{
			return $this->getPageUrl(SessionServerUrlSettings::LOGOUT);
		}
		
		/**
		 * @return SessionServer
		**/
		public function setWapActionPages(SessionServerUrlSettings $pages)
		{
			return $this->setActionPages(CommonLocationSettings::WAP, $pages);
		}
		
		/**
		 * @return SessionServer
		**/
		public function setWebActionPages(SessionServerUrlSettings $pages)
		{
			return $this->setActionPages(CommonLocationSettings::WEB, $pages);
		}
		
		/**
		 * @return SessionServer
		**/
		protected function setActionPages($area, SessionServerUrlSettings $pages)
		{
			$this->actionPages[$area] = $pages;
			
			return $this;
		}
		
		protected function getActionPages($area)
		{
			if (!isset($this->actionPages[$area]))
				throw new WrongArgumentException(
					"actionPages for {{$area}} does not defined"
				);
			
			return $this->actionPages[$area];
		}
		
		protected function getPageUrl($action)
		{
			return
				$this->locations->
					get(Application::me()->getLocationArea())->
						getUrl()
				.(
					$this->
						getActionPages(Application::me()->getLocationArea())->
							getPage($action)
				);
		}
	}
?>