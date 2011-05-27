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

		public function open()
		{
			$this->opened = true;

			return $this;
		}

		public function close()
		{
			$this->opened = false;

			return $this;
		}

		public function basicAck($deliveryTag, $multiple = false)
		{
			return 
				$this->lookupQueue(self::NIL)->
				ack(
					$deliveryTag,
					$multiple === true
						? AMQP_MULTIPLE
						: self::AMQP_NONE
				);
		}

		public function basicCancel($consumerTag)
		{
			return
				$this->lookupQueue(self::NIL)->
				cancel($consumerTag);
		}

		public function basicConsume($queue, $callback)
		{
			throw new UnimplementedFeatureException(
				'http://pecl.php.net/bugs/bug.php?id=22638'
			);
		}

		/**
		 * @return AMQPIncomingMessage
		**/
		public function basicGet($queue, $noAck = true)
		{
			$message =
				$this->lookupQueue($queue)->
				get(
					($noAck === true)
						? AMQP_NOACK
						: self::AMQP_NONE
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
		 * @throws AMQPExchangeException
		**/
		public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		) {
			return
				$this->lookupExchange($exchange)->
				publish(
					$msg->getBody(),
					$routingKey,
					$msg->getBitmask(new AMQPPeclOutgoingMessageBitmask()),
					$msg->getProperties()
				);
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
		 * @throws AMQPExchangeException
		**/
		public function exchangeBind($name, $queue, $routingKey)
		{
			return
				$this->lookupExchange($name)->
				bind($queue, $routingKey);
		}

		public function exchangeUnbind($name, $queue, $routingKey)
		{
			throw new UnimplementedFeatureException();
		}

		/**
		 * @throws AMQPExchangeException
		 * @return boolean
		**/
		public function exchangeDeclare($name, AMQPExchangeConfig $conf)
		{
			if (!$conf->getType() instanceof AMQPExchangeType)
				throw new WrongArgumentException(
					"AMQP exchange type is not set"
				);

			$this->exchangeList[$name] =
				new AMQPExchange($this->transport->getLink());

			return
				$this->exchangeList[$name]->
				declare(
					$name,
					$conf->getType()->getName(),
					$mask = $conf->getBitmask(new AMQPPeclExchangeBitmask())
				);
		}

		/**
		 * @throws AMQPExchangeException
		 * @return boolean
		**/
		public function exchangeDelete(
			$name, $ifUnused = false, $ifEmpty = false
		) {
			$bitmask = self::AMQP_NONE;

			if ($ifUnused)
				$bitmask = $bitmask | AMQP_IFUNUSED;

			if ($ifEmpty)
				$bitmask = $bitmask | AMQP_IFEMPTY;

			$result = 
				$this->lookupExchange($name)->
				delete($name, $bitmask);

			$this->unsetExchange($name);

			return $result;
		}		

		public function queueBind($name, $exchange, $routingKey)
		{
			return
				$this->lookupQueue($name)->
				bind($exchange, $routingKey);
		}

		/**
		 * @throws AMQPQueueException
		 * @return integer - the message count in queue
		**/
		public function queueDeclare($name, AMQPQueueConfig $conf)
		{
			$this->queueList[$name] =
				new AMQPQueue($this->transport->getLink());

			return
				$this->queueList[$name]->
				declare(
					$name,
					$conf->getBitmask(new AMQPPeclQueueBitmask())
				);
		}

		/**
		 * @throws AMQPQueueException
		 * @return boolean
		**/
		public function queueDelete($name)
		{
			$result = 
				$this->lookupQueue($name)->
				delete($name);

			$this->unsetQueue($name);

			return $result;
		}

		/**
		 * @throws AMQPQueueException
		 * @return boolean
		**/
		public function queuePurge($name)
		{
			return 
				$this->lookupQueue($name)->
				purge($name);
		}

		/**
		 * @throws AMQPQueueException
		 * @return boolean
		**/
		public function queueUnbind($name, $exchange, $routingKey)
		{
			return
				$this->lookupQueue($name)->
				unbind($exchange, $routingKey);
		}

		/**
		 * @return AMQPExchange
		**/
		protected function lookupExchange($name)
		{
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
		 * @return AMQPQueue
		**/
		protected function lookupQueue($name)
		{
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
	}
?>