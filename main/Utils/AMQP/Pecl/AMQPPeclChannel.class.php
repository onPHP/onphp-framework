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

		public function isOpen()
		{
			return $this->opened === true;
		}

		/**
		 * @return AMQPPeclChannel
		**/
		public function open()
		{
			$this->opened = true;

			return $this;
		}

		/**
		 * @return AMQPPeclChannel
		**/
		public function close()
		{
			$this->opened = false;

			return $this;
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function basicAck($deliveryTag, $multiple = false)
		{
			$obj = $this->lookupQueue(self::NIL);

			try {
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
				"Could not ack message. No connection available"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerQueueException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function basicCancel($consumerTag)
		{
			$obj = $this->lookupQueue(self::NIL);

			try {
				$result = $obj->cancel($consumerTag);
			} catch (AMQPQueueException $e) {
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			$this->checkCommandResult(
				$result,
				"Could not cancel queue. No connection available"
			);

			return $this;
		}

		public function basicConsume($queue, $callback)
		{
			throw new UnimplementedFeatureException(
				'http://pecl.php.net/bugs/bug.php?id=22638'
			);
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException,
		 * ObjectNotFoundException
		 * 
		 * @return AMQPIncomingMessage
		**/
		public function basicGet($queue, $noAck = true)
		{
			$obj = $this->lookupQueue($queue);

			try {
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
				"Could not get from queue. No connection available"
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
		 * @throws AMQPServerExchangeException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		) {
			$obj = $this->lookupExchange($exchange);

			try {
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
				"Could not publish to exchange. No connection available."
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
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function exchangeBind($name, $queue, $routingKey)
		{
			$obj = $this->lookupExchange($name);

			try {
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
				"Could not bind exchange. No connection available"
			);

			return $this;
		}

		public function exchangeUnbind($name, $queue, $routingKey)
		{
			throw new UnimplementedFeatureException();
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function exchangeDeclare($name, AMQPExchangeConfig $conf)
		{
			$this->checkConnection();

			if (!$conf->getType() instanceof AMQPExchangeType)
				throw new WrongArgumentException(
					"AMQP exchange type is not set"
				);

			$this->exchangeList[$name] =
				new AMQPExchange($this->transport->getLink());

			$obj = $this->exchangeList[$name];

			try {
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
				"Could not declare exchange. No connection available"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function exchangeDelete(
			$name, $ifUnused = false, $ifEmpty = false
		) {
			$bitmask = self::AMQP_NONE;

			if ($ifUnused)
				$bitmask = $bitmask | AMQP_IFUNUSED;

			if ($ifEmpty)
				$bitmask = $bitmask | AMQP_IFEMPTY;

			$obj = $this->lookupExchange($name);

			try {
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
				"Could not delete exchange. No connection available"
			);

			$this->unsetExchange($name);

			return $this;
		}		

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function queueBind($name, $exchange, $routingKey)
		{
			$obj = $this->lookupQueue($name);

			try {
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
				"Could not bind queue. No connection available"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return integer - the message count in queue
		**/
		public function queueDeclare($name, AMQPQueueConfig $conf)
		{
			$this->checkConnection();

			$this->queueList[$name] =
				new AMQPQueue($this->transport->getLink());

			$obj = $this->queueList[$name];

			try {
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
				"Could not declare queue. No connection available."
			);

			return $result;
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function queueDelete($name)
		{
			$obj = $this->lookupQueue($name);

			try {
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
				"Could not delete queue. No connection available"
			);

			$this->unsetQueue($name);

			return $this;
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function queuePurge($name)
		{
			$obj = $this->lookupQueue($name);

			try {
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
				"Could not purge queue. No connection available"
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException, AMQPServerConnectionException
		 * @return AMQPPeclChannel
		**/
		public function queueUnbind($name, $exchange, $routingKey)
		{
			$obj = $this->lookupQueue($name);

			try {
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
				"Could not unbind queue. No connection available"
			);

			return $this;
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