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

	namespace Onphp;

	final class AMQPSelective implements AMQPInterface
	{
		/**
		 * @var array of AMQPChannelInterface instances
		**/
		protected $channels	= array();

		/**
		 * @var string
		 */
		protected static $proxy = '\Onphp\AMQPProxyChannel';

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
		 * @param \Onphp\AMQPPool $pool
		 * @return \Onphp\AMQPSelective
		 */
		public function addPool(AMQPPool $pool)
		{
			foreach ($pool->getList() as $name => $amqp) {
				$this->addLink($name, $amqp);

				if ($name == 'default')
					$this->setCurrent('default');
			}

			return $this;
		}

		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\AMQPPool
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
		 * @throws \Onphp\MissingElementException
		 * @return \Onphp\AMQPPool
		**/
		public function dropLink($name)
		{
			if (!isset($this->pool[$name]))
				throw new MissingElementException(
					"amqp link with name '{$name}' not found"
				);

			unset($this->pool[$name]);

			$this->current = null;

			return $this;
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
		 * @return \Onphp\AMQPInterface
		 * @throws \Onphp\AMQPServerConnectionException
		 */
		public function connect()
		{
			return $this->processMethod('connect');
		}

		/**
		 * @return \Onphp\AMQPInterface
		**/
		public function disconnect()
		{
			return $this->processMethod('disconnect');
		}

		/**
		 * @return \Onphp\AMQPInterface
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
		 * @return \Onphp\AMQPInterface
		**/
		public function getLink()
		{
			return $this->processMethod('getLink');
		}

		/**
		 * @return \Onphp\AMQPCredentials
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
		 * @return \Onphp\AMQPInterface
		 */
		public function setAlive($alive)
		{
			return $this->processMethod('setAlive', $alive);
		}


		/**
		 * @throws \Onphp\WrongArgumentException
		 * @param string $method
		 * @return mixed
		 */
		protected function processMethod($method/*, $args*/)
		{
			$args = func_get_args();
			array_shift($args);

			for ($i = 0; $i < count($this->pool); $i++) {
				try {
					$this->getCurrentItem()->connect();

					return call_user_func_array(
						array($this->getCurrentItem(), $method),
						$args
					);
				} catch (AMQPServerConnectionException $e) {
					$this->setCurrent($this->getAlive());
				}
			}
		}

		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return string
		 */
		public function getAlive()
		{
			foreach ($this->pool as $name => $item) {
				if ($item->isAlive())
					return $name;
			}

			Assert::isUnreachable("no alive connection");
		}

		/**
		 * @param string $name
		 * @return \Onphp\AMQPSelective
		 */
		public function setCurrent($name)
		{
			Assert::isIndexExists($this->pool, $name);
			
			$this->current = $name;

			return $this;
		}

		/**
		 * @thows WrongArgumentException
		 * @return \Onphp\AMQPInterface
		 */
		protected function getCurrentItem()
		{
			if ($this->current && $this->pool[$this->current]->isAlive())
				return $this->pool[$this->current];

			Assert::isUnreachable("no current connection");
		}
	}
?>