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

	final class AMQPPeclChannel extends AMQPBaseChannel
	{
		const NIL = 'nil';
		const AMQP_NONE = 0;

		protected $exchangeList = array();
		protected $queueList = array();
		protected $opened = false;

		/**
		 * @var AMQPConsumer
		**/
		protected $consumer = null;

		public function isOpen()
		{
			return $this->opened === true;
		}

		/**
		 * @return AMQPChannelInterface
		**/
		public function open()
		{
			$this->opened = true;

			return $this;
		}

		/**
		 * @return AMQPChannelInterface
		**/
		public function close()
		{
			$this->opened = false;

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function basicAck($deliveryTag, $multiple = false)
		{
			try {
				$obj = $this->lookupQueue(self::NIL);
				$result = $obj->ack(
					$deliveryTag,
					$multiple === true
						? AMQP_MULTIPLE
						: self::AMQP_NONE
				);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not ack message"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerQueueException|AMQPServerConnectionException|WrongStateException
		 * @return AMQPChannelInterface
		**/
		public function basicCancel($consumerTag)
		{
			if (!$this->consumer instanceof AMQPConsumer)
				throw new WrongStateException();

			try {
				$obj = $this->lookupQueue($this->consumer->getQueueName());
				$result = $obj->cancel($consumerTag);
				$this->consumer->handleCancelOk($consumerTag);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not cancel queue"
			);

			return $this;
		}

		/**
		 * PECL AMQP does not implement basicConsume logic, we'll emulate it.
		 *
		 * @return AMQPChannelInterface
		**/
		public function basicConsume($queue, $autoAck, AMQPConsumer $callback)
		{
			$this->consumer =
				$callback->
					setQueueName($queue)->
					setAutoAcknowledge($autoAck === true);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException|ObjectNotFoundException
		 * @return AMQPIncomingMessage
		**/
		public function basicGet($queue, $noAck = true)
		{
			try {
				$obj = $this->lookupQueue($queue);
				$message = $obj->get(
					($noAck === true)
						? AMQP_NOACK
						: self::AMQP_NONE
				);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				is_array($message),
				"Could not get from queue"
			);

			if (
				isset($message[AMQPIncomingMessage::COUNT])
				&& $message[AMQPIncomingMessage::COUNT] == -1
			)
				throw new ObjectNotFoundException(
					"AMQP queue with name '{$queue}' is empty"
				);

			return AMQPIncomingMessage::spawn($message);
		}

		/**
		 * @throws AMQPServerExchangeException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		) {
			try {
				$obj = $this->lookupExchange($exchange);
				$result = $obj->publish(
					$msg->getBody(),
					$routingKey,
					$msg->getBitmask(new AMQPPeclOutgoingMessageBitmask()),
					$msg->getProperties()
				);
			} catch (AMQPExchangeException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not publish to exchange"
			);

			return $this;
		}

		public function basicQos($prefetchSize, $prefetchCount)
		{
			throw new UnimplementedFeatureException();
		}

		public function exchangeToExchangeBind(
			$destination, $source, $routingKey
		)
		{
			throw new UnimplementedFeatureException(
				'Exchange to exchange bindings is not yet implemented'
			);
		}

		public function exchangeToExchangeUnbind(
			$destination, $source, $routingKey
		)
		{
			throw new UnimplementedFeatureException(
				'Exchange to exchange unbindings is not yet implemented'
			);
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function exchangeBind($name, $queue, $routingKey)
		{
			try {
				$obj = $this->lookupExchange($name);
				$result = $obj->bind($queue, $routingKey);
			} catch (AMQPExchangeException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not bind exchange"
			);

			return $this;
		}

		public function exchangeUnbind($name, $queue, $routingKey)
		{
			throw new UnimplementedFeatureException();
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function exchangeDeclare($name, AMQPExchangeConfig $conf)
		{
			$this->checkConnection();

			if (!$conf->getType() instanceof AMQPExchangeType)
				throw new WrongArgumentException(
					"AMQP exchange type is not set"
				);

			try {
				$this->exchangeList[$name] =
					new AMQPExchange($this->transport->getLink());

				$obj = $this->exchangeList[$name];

				$result = $obj->declare(
					$name,
					$conf->getType()->getName(),
					$conf->getBitmask(new AMQPPeclExchangeBitmask())
				);
			} catch (AMQPExchangeException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not declare exchange"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function exchangeDelete(
			$name, $ifUnused = false, $ifEmpty = false
		) {
			$bitmask = self::AMQP_NONE;

			if ($ifUnused)
				$bitmask = $bitmask | AMQP_IFUNUSED;

			if ($ifEmpty)
				$bitmask = $bitmask | AMQP_IFEMPTY;

			try {
				$obj = $this->lookupExchange($name);
				$result = $obj->delete($name, $bitmask);
			} catch (AMQPExchangeException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not delete exchange"
			);

			$this->unsetExchange($name);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function queueBind($name, $exchange, $routingKey)
		{
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->bind($exchange, $routingKey);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not bind queue"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return integer - the message count in queue
		**/
		public function queueDeclare($name, AMQPQueueConfig $conf)
		{
			$this->checkConnection();

			try {
				$this->queueList[$name] =
					new AMQPQueue($this->transport->getLink());

				$obj = $this->queueList[$name];

				$result = $obj->declare(
					$name,
					$conf->getBitmask(new AMQPPeclQueueBitmask())
				);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				is_int($result),
				"Could not declare queue"
			);

			return $result;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function queueDelete($name)
		{
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->delete($name);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not delete queue"
			);

			$this->unsetQueue($name);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function queuePurge($name)
		{
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->purge($name);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not purge queue"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @return AMQPChannelInterface
		**/
		public function queueUnbind($name, $exchange, $routingKey)
		{
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->unbind($exchange, $routingKey);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not unbind queue"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException|WrongStateException
		 * @return AMQPIncomingMessage
		**/
		public function getNextDelivery()
		{
			if (!$this->consumer instanceof AMQPConsumer)
				throw new WrongStateException();

			try {
				$obj = $this->lookupQueue(
					$this->consumer->getQueueName()
				);

				$messages = $obj->consume(
					array(
						'min' => 1,
						'max' => 1,
						'ack' => (bool) $this->consumer->isAutoAcknowledge(),
					)
				);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				is_array($messages),
				"Could not consume from queue"
			);

			$message = array_shift($messages);
			$incoming = AMQPIncomingMessage::spawn($message);

			if ($this->consumer->getConsumerTag() === null) {
				$this->consumer->setConsumerTag($incoming->getConsumerTag());
				$this->consumer->handleConsumeOk($incoming->getConsumerTag());
			} else if (
				$this->consumer->getConsumerTag()
				!= $incoming->getConsumerTag()
			) {
				throw new WrongStateException(
					"Consumer change tag consumerTag="
					."{$this->consumer->getConsumerTag()}, "
					."message.consumerTag={$incoming->getConsumerTag()}, "
					."message.body={$incoming->getBody()}"
				);
			}

			$this->consumer->handleDelivery($incoming);

			return $incoming;
		}

		/**
		 * @throws AMQPServerConnectionException
		 * @return AMQPExchange
		**/
		protected function lookupExchange($name)
		{
			$this->checkConnection();

			if (!isset($this->exchangeList[$name])) {
				$this->exchangeList[$name] =
					new AMQPExchange($this->transport->getLink(), $name);
			}

			return $this->exchangeList[$name];
		}

		/**
		 * @return AMQPPeclChannel
		**/
		protected function unsetExchange($name)
		{
			if (isset($this->exchangeList[$name]))
				unset($this->exchangeList[$name]);

			return $this;
		}

		/**
		 * @throws AMQPServerConnectionException
		 * @return AMQPQueue
		**/
		protected function lookupQueue($name)
		{
			$this->checkConnection();

			if (!isset($this->queueList[$name])) {
				$this->queueList[$name] =
					($name == self::NIL)
						? new AMQPQueue($this->transport->getLink())
						: new AMQPQueue($this->transport->getLink(), $name);
			}

			return $this->queueList[$name];
		}

		/**
		 * @return AMQPPeclChannel
		**/
		protected function unsetQueue($name)
		{
			if (isset($this->queueList[$name]))
				unset($this->queueList[$name]);

			return $this;
		}

		/**
		 * @throws AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		protected function checkCommandResult($boolean, $message)
		{
			if ($boolean !== true) {
				//link is not alive!!!
				$this->transport->getLink()->disconnect();
				throw new AMQPServerConnectionException($message);
			}

			return $this;
		}
	}
?>