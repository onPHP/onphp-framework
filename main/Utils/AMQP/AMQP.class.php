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
	 * AMQP stands for Advanced Message Queue Protocol, which is
	 * an open standard middleware layer for message routing and queuing.
	**/
	namespace Onphp;

	abstract class AMQP implements AMQPInterface
	{
		/**
		 * @var \Onphp\AMQPCredentials
		**/
		protected $credentials = null;
		protected $link	= null;
		protected $alive = true;

		/**
		 * @var array of AMQPChannelInterface instances
		**/
		protected $channels	= array();

		/**
		 * @return \Onphp\AMQP
		**/
		abstract public function connect();

		/**
		 * @return \Onphp\AMQP
		**/
		abstract public function disconnect();
		
		/**
		 * @return \Onphp\AMQP
		**/
		abstract public function reconnect();

		/**
		 * @return boolean
		**/
		abstract public function isConnected();

		/**
		 * @return \Onphp\AMQPChannelInterface
		 */
		abstract public function spawnChannel($id, AMQPInterface $transport);
		
		public function __construct(AMQPCredentials $credentials)
		{
			$this->credentials = $credentials;
		}

		public function __destruct()
		{
			if ($this->isConnected()) {
				$this->disconnect();
			}
		}

		/**
		 * @return \Onphp\AMQP
		**/
		public static function spawn($class, AMQPCredentials $credentials)
		{
			return new $class($credentials);
		}

		/**
		 * @return \Onphp\AMQP
		**/
		public function getLink()
		{
			return $this->link;
		}

		/**
		 * @param integer $id
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function createChannel($id)
		{
			Assert::isInteger($id);

			if (isset($this->channels[$id]))
				throw new WrongArgumentException(
					"AMQP channel with id '{$id}' already registered"
				);
			
			if (!$this->isConnected())
				$this->connect();
			
			$this->channels[$id] = 
				$this->spawnChannel($id, $this)->
				open();

			return $this->channels[$id];
		}

		/**
		 * @throws \Onphp\MissingElementException
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function getChannel($id)
		{
			if (isset($this->channels[$id]))
				return $this->channels[$id];

			throw new MissingElementException(
				"Can't find AMQP channel with id '{$id}'"
			);
		}

		/**
		 * @return array
		**/
		public function getChannelList()
		{
			return $this->channels;
		}

		/**
		 * @param integer $id
		 * @throws \Onphp\MissingElementException
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function dropChannel($id)
		{
			if (!isset($this->channels[$id]))
				throw new MissingElementException(
					"AMQP channel with id '{$id}' not found"
				);

			$this->channels[$id]->close();

			unset($this->channels[$id]);

			return $this;
		}

		/**
		 * @return \Onphp\AMQPCredentials
		 */
		public function getCredentials()
		{
			return $this->credentials;
		}

		/**
		 * @return bool
		 */
		public function isAlive()
		{
			return $this->alive;
		}

		/**
		 * @param bool $alive
		 * @return \Onphp\AMQP
		 */
		public function setAlive($alive)
		{
			$this->alive = ($alive === true);

			return $this;
		}


	}
?>