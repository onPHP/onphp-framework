<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class SessionServerUrlSettings
	{
		const REGISTRATION	= 'registration';
		const PROFILE		= 'profile';
		const LOGIN			= 'login';
		const LOGOUT		= 'logout';

		private $pages = array(
			self::REGISTRATION	=> null,
			self::PROFILE		=> null,
			self::LOGIN			=> null,
			self::LOGOUT		=> null
		);

		public function create()
		{
			return new self;
		}

		public function setRegistrationPage($registrationPage)
		{
			return $this->setPage(self::REGISTRATION, $registrationPage);
		}

		public function getRegistrationPage()
		{
			return $this->getPage(self::REGISTRATION);
		}

		public function setProfilePage($profilePage)
		{
			return $this->setPage(self::PROFILE, $profilePage);
		}

		public function getProfilePage()
		{
			return $this->getPage(self::PROFILE);
		}

		public function setLoginPage($loginPage)
		{
			return $this->setPage(self::LOGIN, $loginPage);
		}

		public function getLoginPage()
		{
			return $this->getPage(self::LOGIN);
		}

		public function setLogoutPage($logoutPage)
		{
			return $this->setPage(self::LOGOUT, $logoutPage);
		}

		public function getLogoutPage()
		{
			return $this->getPage(self::LOGOUT);
		}

		protected function setPage($action, $page)
		{
			$this->pages[$action] = $page;

			return $this;
		}

		protected function getPage($action)
		{
			if (!isset($this->pages[$action]))
				throw new WrongArgumentException("page for {{$action}} does not defined");

			return $this->pages[$action];
		}
	}
?>