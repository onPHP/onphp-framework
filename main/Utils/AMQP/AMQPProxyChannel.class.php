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

	/**
	 * Base class modelling an AMQ channel
	**/
	namespace Onphp;

	class AMQPProxyChannel implements AMQPChannelInterface
	{
		/**
		 * @var \Onphp\AMQPChannelInterface
		 */
		protected $channel = null;

		public function __construct(AMQPChannelInterface $channel)
		{
			$this->channel = $channel;
		}

		/**
		 * @return true
		**/
		public function isOpen()
		{
			return $this->channel->isOpen();
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function open()
		{
			return $this->channel->open();
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function close()
		{
			return $this->channel->close();
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeDeclare($name, AMQPExchangeConfig $conf)
		{
			try {
				return $this->channel->exchangeDeclare($name, $conf);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					exchangeDeclare($name, $conf);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeDelete($name, $ifUnused = false)
		{
			try {
				return $this->channel->exchangeDelete($name, $ifUnused);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					exchangeDelete($name, $ifUnused);
			}
		}

		public function exchangeBind($destinationName, $sourceName, $routingKey)
		{
			try {
				return $this->channel->exchangeBind(
					$destinationName,
					$sourceName,
					$routingKey
				);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					exchangeBind(
						$destinationName,
						$sourceName,
						$routingKey
					);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeUnbind($destinationName, $sourceName, $routingKey)
		{
			try {
				return $this->channel->exchangeUnbind(
					$destinationName,
					$sourceName,
					$routingKey
				);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					exchangeUnbind(
						$destinationName,
						$sourceName,
						$routingKey
					);
			}
		}

		/**
		 * @return int
		**/
		public function queueDeclare($name, AMQPQueueConfig $conf)
		{
			try {
				return $this->channel->queueDeclare($name, $conf);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					queueDeclare($name, $conf);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queueBind($name, $exchange, $routingKey)
		{
			try {
				return $this->channel->queueBind(
					$name,
					$exchange,
					$routingKey
				);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					queueBind(
						$name,
						$exchange,
						$routingKey
					);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queueUnbind($name, $exchange, $routingKey)
		{
			try {
				return $this->channel->queueUnbind(
					$name,
					$exchange,
					$routingKey
				);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					queueUnbind(
						$name,
						$exchange,
						$routingKey
					);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queuePurge($name)
		{
			try {
				return $this->channel->queuePurge($name);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					queuePurge($name);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queueDelete($name)
		{
			try {
				return $this->channel->queueDelete($name);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					queueDelete($name);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicPublish($exchange, $routingKey, AMQPOutgoingMessage $msg)
		{
			try {
				return $this->channel->basicPublish(
					$exchange,
					$routingKey,
					$msg
				);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					basicPublish($exchange, $routingKey, $msg);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicQos($prefetchSize, $prefetchCount)
		{
			try {
				return $this->channel->basicQos($prefetchSize, $prefetchCount);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					basicQos($prefetchSize, $prefetchCount);
			}
		}

		/**
		 * @return \Onphp\AMQPIncomingMessage
		**/
		public function basicGet($queue, $autoAck = true)
		{
			try {
				return $this->channel->basicGet($queue, $autoAck);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					basicGet($queue, $autoAck);
			}
		}


		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicAck($deliveryTag, $multiple = false)
		{
			try {
				return $this->channel->basicAck($deliveryTag, $multiple);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					basicAck($deliveryTag, $multiple);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicConsume($queue, $autoAck, AMQPConsumer $callback)
		{
			try {
				return $this->channel->basicConsume($queue, $autoAck, $callback);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					basicConsume($queue, $autoAck, $callback);
			}
		}

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicCancel($consumerTag)
		{
			try {
				return $this->channel->basicCancel($consumerTag);
			} catch (AMQPServerException $e) {
				return $this->
					transportReconnect($e)->
					basicCancel($consumerTag);
			}
		}

		/**
		 * @throws \Onphp\AMQPServerException
		 * @param \Exception $e
		 * @return \Onphp\AMQPProxyChannel
		 */
		protected function transportReconnect(\Exception $e)
		{
			$this->markAlive(false);

			$this->reconnect($e);

			return $this;
		}

		private function markAlive($alive = false)
		{
			try {
				$this->channel->getTransport()->
					setAlive($alive);
			} catch (WrongArgumentException $e) {/*no_connection*/}

			return $this;
		}

		/**
		 * @return \Onphp\AMQPProxyChannel
		 * @throws \Onphp\AMQPServerException
		 */
		private function reconnect(\Exception $amqpException)
		{
			try {
				$this->channel->getTransport()->
					setCurrent(
						$this->channel->getTransport()->getAlive()
					);
			} catch (WrongArgumentException $e) {
				throw new AMQPServerException(
					$amqpException->getMessage(),
					$amqpException->getCode(),
					$amqpException
				);
			}

			return $this;
		}
	}
?>