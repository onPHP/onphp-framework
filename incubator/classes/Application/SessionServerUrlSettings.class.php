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
		
		/**
		 * @return SessionServerUrlSettings
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return SessionServerUrlSettings
		**/
		public function setRegistrationPage($registrationPage)
		{
			return $this->setPage(self::REGISTRATION, $registrationPage);
		}
		
		public function getRegistrationPage()
		{
			return $this->getPage(self::REGISTRATION);
		}
		
		/**
		 * @return SessionServerUrlSettings
		**/
		public function setProfilePage($profilePage)
		{
			return $this->setPage(self::PROFILE, $profilePage);
		}
		
		public function getProfilePage()
		{
			return $this->getPage(self::PROFILE);
		}
		
		/**
		 * @return SessionServerUrlSettings
		**/
		public function setLoginPage($loginPage)
		{
			return $this->setPage(self::LOGIN, $loginPage);
		}
		
		public function getLoginPage()
		{
			return $this->getPage(self::LOGIN);
		}
		
		/**
		 * @return SessionServerUrlSettings
		**/
		public function setLogoutPage($logoutPage)
		{
			return $this->setPage(self::LOGOUT, $logoutPage);
		}
		
		public function getLogoutPage()
		{
			return $this->getPage(self::LOGOUT);
		}
		
		/**
		 * @return SessionServerUrlSettings
		**/
		protected function setPage($action, $page)
		{
			$this->pages[$action] = $page;
			
			return $this;
		}
		
		protected function getPage($action)
		{
			if (!isset($this->pages[$action]))
				throw new WrongArgumentException(
					"page for {{$action}} does not defined"
				);
			
			return $this->pages[$action];
		}
	}
?>