<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Scheglov K.                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Quite common information collection.
	 * 
	 * @ingroup Helpers
	**/
	class Credentials
	{
		private $host		= null;
		private $port		= 80;
		private $username	= null;
		private $password	= null;
		
		public static function create()
		{
			return new self;
		}
		
		public function import($host, $port, $username, $password)
		{
			return
				$this->
					setHost($host)->
					setPort($port)->
					setUsername($username)->
					setPassword($password);
		}
		
		public function setUsername($username)
		{
			$this->username = $username;

			return $this;
		}

		public function getUsername()
		{
			return $this->username;
		}

		public function setPassword($password)
		{
			$this->password = $password;

			return $this;
		}

		public function getPassword()
		{
			return $this->password;
		}

		public function setHost($host)
		{
			$this->host = $host;

			return $this;
		}

		public function getHost()
		{
			return $this->host;
		}

		public function setPort($port)
		{
			if (($port > 0) && ($port < 65536))
				$this->post = $port;
			else
				throw new WrongArgumentException(
					'invalid port number specified'
				);

			return $this;
		}

		public function getPort()
		{
			return $this->port;
		}
	}
?>