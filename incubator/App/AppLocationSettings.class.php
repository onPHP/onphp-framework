<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class AppLocationSettings
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

		public static function create()
		{
			return new self;
		}

		public function setWeb(AppUrl $webLocation)
		{
			return $this->set(self::WEB, $webLocation);
		}

		public function getWeb()
		{
			return $this->get(self::WEB);
		}

		public function setWebUrl($url)
		{
			return $this->setWebUrl(AppUrl::create()->setUrl($url));
		}

		public function getWebUrl()
		{
			return $this->getWeb()->getUrl();
		}

		public function setWap(AppUrl $wapLocation)
		{
			return $this->set(self::WAP, $wapLocation);
		}

		public function getWap()
		{
			return $this->get(self::WAP);
		}

		public function setWapUrl($url)
		{
			return $this->setWapUrl(AppUrl::create()->setUrl($url));
		}

		public function getWapUrl()
		{
			return $this->getWap()->getUrl();
		}

		public function setAdmin(AppUrl $adminLocation)
		{
			return $this->set(self::ADMIN, $adminLocation);
		}

		public function getAdmin()
		{
			return $this->get(self::ADMIN);
		}

		public function setAdminUrl($url)
		{
			return $this->setAdminUrl(AppUrl::create()->setUrl($url));
		}

		public function getAdminUrl()
		{
			return $this->getAdmin()->getUrl();
		}

		public function setSoap(AppUrl $soapLocation)
		{
			return $this->set(self::SOAP, $soapLocation);
		}

		public function getSoap()
		{
			return $this->get(self::SOAP);
		}

		public function setSoapUrl($url)
		{
			return $this->setSoapUrl(AppUrl::create()->setUrl($url));
		}

		public function getSoapUrl()
		{
			return $this->getSoap()->getUrl();
		}

		public function set($area, AppUrl $location)
		{
			$this->locations[$area] = $location;

			return $this;
		}

		public function get($area)
		{
			if (!isset($this->locations[$area]))
				throw new WrongArgumentException("location {{$area}} does not defined");

			return $this->locations[$area];
		}
	}
?>