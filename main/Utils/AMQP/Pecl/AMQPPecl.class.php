<?php
/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see http://www.php.net/manual/en/book.amqp.php
	**/
	final class AMQPPecl extends AMQP
	{
		public function __construct(AMQPCredentials $credentials)
		{
			parent::__construct($credentials);

			$this->fillCredentials();
		}

		/**
		 * @return boolean
		**/
		public function isConnected()
		{
			return $this->link->isConnected();
		}

		/**
		 * @throws AMQPConnectionException
		 * @return boolean
		**/
		public function connect()
		{
			if ($this->isConnected())
				return true;

			return $this->link->connect();
		}
		
		/**
		 * @return boolean
		**/
		public function disconnect()
		{
			if ($this->isConnected())
				return $this->link->disconnect();

			return false;
		}

		/**
		 * @param mixed $id
		 * @param AMQP $transport
		 * @return AMQPPeclChannel
		**/
		protected function spawnChannel($id, AMQP $transport)
		{
			return new AMQPPeclChannel($id, $transport);
		}

		/**
		 * @return AMQPPecl
		**/
		protected function fillCredentials()
		{
			$this->link = new AMQPConnection();
			$this->link->setHost($this->credentials->getHost());
			$this->link->setPort($this->credentials->getPort());
			$this->link->setLogin($this->credentials->getLogin());
			$this->link->setPassword($this->credentials->getPassword());
			$this->link->setVHost($this->credentials->getVirtualHost());

			return $this;
		}
	}
?>
