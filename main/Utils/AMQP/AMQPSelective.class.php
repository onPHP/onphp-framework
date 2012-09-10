<?php
/***************************************************************************
 *   Copyright (C) 2012 by Evgeniya Tekalin                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class AMQPSelective implements AMQPInterface
	{
		/**
		 * @var array of AMQPChannelInterface instances
		**/
		protected $channels	= array();

		/**
		 * @var string
		 */
		protected static $proxy = 'AMQPProxyChannel';

		/**
		 * @var sting
		 */
		private $current = null;
		private $pool = array();

		/**
		 * @return AMQPAgregate
		**/
		public static function me()
		{
			return new self;
		}

		/**
		 * @param string $proxy
		 */
		public static function setProxy($proxy)
		{
			self::$proxy = $proxy;
		}

		/**
		 * @throws WrongArgumentException
		 * @return AMQPPool
		**/
		public function addLink($name, AMQP $amqp)
		{
			if (isset($this->pool[$name]))
				throw new WrongArgumentException(
					"amqp link with name '{$name}' already registered"
				);

			if ($this->pool)
				Assert::isInstance($amqp, current($this->pool));

			$this->pool[$name] = $amqp;

			return $this;
		}

		/**
		 * @throws MissingElementException
		 * @return AMQPPool
		**/
		public function dropLink($name)
		{
			if (!isset($this->pool[$name]))
				throw new MissingElementException(
					"amqp link with name '{$name}' not found"
				);

			unset($this->pool[$name]);

			$this->current = null;

			echo "\namq: dropLink {$name}";

			return $this;
		}

		/**
		 * @param integer $id
		 * @throws WrongArgumentException
		 * @return AMQPChannelInterface
		**/
		public function createChannel($id)
		{
			Assert::isInteger($id);

			if (isset($this->channels[$id]))
				throw new WrongArgumentException(
					"AMQP channel with id '{$id}' already registered"
				);

			if (!$this->current)
				$this->setCurrent($this->getAlive());
			
			if (!$this->isConnected())
				$this->connect();

			$this->channels[$id] = new self::$proxy(
				$this->getCurrentItem()->spawnChannel($id, $this)
			);
			
			$this->channels[$id]->open();

			return $this->channels[$id];
		}

		/**
		 * @throws MissingElementException
		 * @return AMQPChannelInterface
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
		 * @throws MissingElementException
		 * @return AMQPChannelInterface
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
		 * @return AMQPInterface
		 * @throws AMQPServerConnectionException
		 */
		public function connect()
		{
			return $this->processMethod('connect');
		}

		/**
		 * @return AMQPInterface
		**/
		public function disconnect()
		{
			return $this->processMethod('disconnect');
		}

		/**
		 * @return AMQPInterface
		**/
		public function reconnect()
		{
			return $this->processMethod('reconnect');
		}

		/**
		 * @return boolean
		**/
		public function isConnected()
		{
			return $this->processMethod('isConnected');
		}


		/**
		 * @return AMQPInterface
		**/
		public function getLink()
		{
			return $this->processMethod('getLink');
		}

		/**
		 * @return AMQPCredentials
		 */
		public function getCredentials()
		{
			return $this->processMethod('getCredentials');
		}


		/**
		 * @return bool
		 */
		public function isAlive()
		{
			return $this->processMethod('isAlive');
		}

		/**
		 * @param bool $alive
		 * @return AMQPInterface
		 */
		public function setAlive($alive)
		{
			return $this->processMethod('setAlive', $alive);
		}


		/**
		 * @throws WrongArgumentException
		 * @param string $method
		 * @return mixed
		 */
		protected function processMethod($method/*, $args*/)
		{
			$args = func_get_args();
			array_shift($args);

//			try {
				for ($i = 0; $i < count($this->pool); $i++) {
					try {

						echo "\namqp: {$method}[{$this->getCurrentItem()->getCredentials()->getPort()}] (".join(',', $args).")";

						$this->getCurrentItem()->connect();
						
						return call_user_func_array(
							array($this->getCurrentItem(), $method),
							$args
						);
					} catch (AMQPServerConnectionException $e) {
						$this->setCurrent($this->getAlive());
					}
				}
//			} catch (WrongStateException $e) {
//				throw new AMQPServerConnectionException($e->getMessage());
//			}
		}

		/**
		 * @throws WrongArgumentException
		 * @return string
		 */
		public function getAlive()
		{
			foreach ($this->pool as $name => $item) {
				//echo "\n$name isAlive: {$item->isAlive()}";
				if ($item->isAlive())
					return $name;
			}

			Assert::isUnreachable("no alive connection");
		}

		/**
		 * @param string $name
		 * @return AMQPSelective
		 */
		public function setCurrent($name)
		{
			echo "\namqp: setCurrent($name) isAlive: {$this->pool[$name]->isAlive()}";

			Assert::isIndexExists($this->pool, $name);
			
			$this->current = $name;

			return $this;
		}

		/**
		 * @thows WrongArgumentException
		 * @return AMQPInterface
		 */
		protected function getCurrentItem()
		{
			if ($this->current && $this->pool[$this->current]->isAlive())
				return $this->pool[$this->current];

			Assert::isUnreachable("no current connection");
		}
	}
?>