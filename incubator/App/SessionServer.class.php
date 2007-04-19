<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class SessionServer extends Singleton implements Instantiatable
	{
		private $locations	= null;
		private $timeout	= null;

		private $actionPages = array();
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function setLocations(AppLocationSettings $locations)
		{
			$this->locations = $locations;

			return $this;
		}

		public function getLocationSettings()
		{
			return $this->locations;
		}

		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;

			return $this;
		}

		public function getTimeout()
		{
			return $this->timeout;
		}

		public function getUrl()
		{
			return $this->locations->getSoap()->getUrl();
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

		public function setWapActionPages(SessionServerUrlSettings $pages)
		{
			return $this->setActionPages(AppLocationSettings::WAP, $pages);
		}

		public function setWebActionPages(SessionServerUrlSettings $pages)
		{
			return $this->setActionPages(AppLocationSettings::WEB, $pages);
		}

		protected function setActionPages($area, SessionServerUrlSettings $pages)
		{
			$this->actionPages[$area] = $pages;

			return $this;
		}

		protected function getActionPages($area)
		{
			if (!isset($this->actionPages[$area]))
				throw new WrongArgumentException("actionPages for {{$area}} does not defined");

			return $this->actionPages[$area];
		}

		protected function getPageUrl($action)
		{
			return
				$this->locations->
					get(App::me()->getLocationArea())->
						getBaseUrl()
				.(
					$this->
						getActionPages(App::me()->getLocationArea())->
							getPage($action)
				);
		}
	}
?>