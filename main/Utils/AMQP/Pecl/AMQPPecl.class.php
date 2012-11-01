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
	namespace Onphp;

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
			try {
				return $this->link->isConnected();
			} catch (\Exception $e) {
				return false;
			}
		}

		/**
		 * @throws \Onphp\AMQPServerConnectionException
		 * @return \Onphp\AMQP
		**/
		public function connect()
		{
			try {
				if ($this->isConnected())
					return $this;

				$this->link->connect();

			} catch (\AMQPConnectionException $e) {
				$this->alive = false;

				throw new AMQPServerConnectionException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			return $this;
		}

		/**
		 * @throws \Onphp\AMQPServerConnectionException
		 * @return \Onphp\AMQP
		**/
		public function reconnect()
		{
			try {
				$this->link->reconnect();
				return $this;
			} catch (\AMQPConnectionException $e) {
				$this->alive = false;

				throw new AMQPServerConnectionException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}
		}
		
		/**
		 * @throws \Onphp\AMQPServerConnectionException
		 * @return \Onphp\AMQP
		**/
		public function disconnect()
		{
			try {
				if ($this->isConnected()) {
					$this->link->disconnect();
					return $this;
				}
			} catch (\AMQPConnectionException $e) {
				$this->alive = false;
				
				throw new AMQPServerConnectionException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}
		}

		/**
		 * @param mixed $id
		 * @param \Onphp\AMQPInterface $transport
		 * @return \Onphp\AMQPPeclChannel
		**/
		public function spawnChannel($id, AMQPInterface $transport)
		{
			return new AMQPPeclChannel($id, $transport);
		}

		/**
		 * @return \Onphp\AMQPPecl
		**/
		protected function fillCredentials()
		{
			$this->link = new \AMQPConnection();
			$this->link->setHost($this->credentials->getHost());
			$this->link->setPort($this->credentials->getPort());
			$this->link->setLogin($this->credentials->getLogin());
			$this->link->setPassword($this->credentials->getPassword());
			$this->link->setVHost($this->credentials->getVirtualHost());

			return $this;
		}
	}
?>
