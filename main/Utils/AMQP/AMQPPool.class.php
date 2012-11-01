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
	 * @todo BasePool implementations
	**/
	namespace Onphp;

	final class AMQPPool extends Singleton implements Instantiatable
	{
		private $default = null;
		private $pool = array();

		/**
		 * @return \Onphp\AMQPPool
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return \Onphp\AMQPPool
		**/
		public function setDefault(AMQP $amqp)
		{
			$this->default = $amqp;

			return $this;
		}

		/**
		 * @return \Onphp\AMQPPool
		**/
		public function dropDefault()
		{
			$this->default = null;

			return $this;
		}

		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\AMQPPool
		**/
		public function addLink($name, AMQP $amqp)
		{
			if (isset($this->pool[$name]))
				throw new WrongArgumentException(
					"amqp link with name '{$name}' already registered"
				);

			$this->pool[$name] = $amqp;

			return $this;
		}

		/**
		 * @throws MissingElementException
		 * @return \Onphp\AMQPPool
		**/
		public function dropLink($name)
		{
			if (!isset($this->pool[$name]))
				throw new MissingElementException(
					"amqp link with name '{$name}' not found"
				);

			unset($this->pool[$name]);

			return $this;
		}

		/**
		 * @throws MissingElementException
		 * @return \Onphp\AMQP
		**/
		public function getLink($name = null)
		{
			$link = null;

			// single-amqp project
			if (!$name) {
				if (!$this->default)
					throw new MissingElementException(
						'i have no default amqp link and '
						.'requested link name is null'
					);

				$link = $this->default;
			} elseif (isset($this->pool[$name]))
				$link = $this->pool[$name];

			if ($link) {
				if (!$link->isConnected())
					$link->connect();

				return $link;
			}

			throw new MissingElementException(
				"can't find amqp link with '{$name}' name"
			);
		}

		/**
		 * @return \Onphp\AMQPPool
		**/
		public function shutdown()
		{
			$this->disconnect();

			$this->default = null;
			$this->pool = array();

			return $this;
		}

		/**
		 * @return \Onphp\AMQPPool
		**/
		public function disconnect()
		{
			if ($this->default)
				$this->default->disconnect();

			foreach ($this->pool as $amqp)
				$amqp->disconnect();

			return $this;
		}

		/**
		 * @return array
		 */
		public function getList()
		{
			$list = $this->pool;

			try {
				$list['default'] = $this->getLink();
			} catch (MissingElementException $e) {/**/}

			return $list;
		}
	}
?>