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
		const AMQP_NONE = AMQP_NOPARAM;

		protected $exchangeList = array();
		protected $queueList = array();
		protected $opened = false;


        /**
         * @var AMQPChannel
         */
        protected $link = null;

		/**
		 * @var AMQPConsumer
		**/
		protected $consumer = null;

        public function __construct($id, AMQPInterface $transport)
        {
            parent::__construct($id, $transport);
        }

		public function isOpen()
		{
			echo "\nchannel: isOpen";
			return $this->opened === true;
		}

		/**
		 * @return AMQPChannelInterface
		**/
		public function open()
		{
			echo "\nchannel: open";
			$this->opened = true;

			return $this;
		}

		/**
		 * @return AMQPChannelInterface
		**/
		public function close()
		{
			echo "\nchannel: close";
			$this->opened = false;

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @param sting $deliveryTag
		 * @param bool $multiple
		 * @return AMQPPeclChannel
		 */
		public function basicAck($deliveryTag, $multiple = false)
		{
			echo "\nchannel: basicAck";
			try {
				$obj = $this->lookupQueue(self::NIL);
				$result = $obj->ack(
					$deliveryTag,
					$multiple === true
						? AMQP_MULTIPLE
						: self::AMQP_NONE
				);
			} catch (AMQPQueueException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
		 * can't get $consumerTag
		 * @throws AMQPServerQueueException|AMQPServerConnectionException|WrongStateException
		 * @param string $consumerTag
		 * @return AMQPPeclChannel
		 */
		public function basicCancel($consumerTag)
		{
			echo "\nchannel: basicCancel";

			if (!$this->consumer instanceof AMQPConsumer)
				throw new WrongStateException();

			try {
				$obj = $this->lookupQueue($consumerTag);

				$result = $obj->cancel($consumerTag);

			} catch (AMQPQueueException $e) {
				$this->clearConnection();
				
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
		 * @return AMQPChannelInterface
		**/
		public function basicConsume($queue, $autoAck, AMQPConsumer $callback)
		{
			echo "\nchannel: basicConsume";
			Assert::isInstance($callback, 'AMQPPeclQueueConsumer');

			$this->consumer = $callback->
				setQueueName($queue)->
				setAutoAcknowledge($autoAck === true);
			
			$obj = $this->lookupQueue($queue);

			$this->consumer->handleConsumeOk(
				$this->consumer->getConsumerTag()
			);

			/**
			 * blocking function
			 */
			$obj->consume(
				array($callback, 'handlePeclDelivery'),
				$autoAck 
					? AMQP_AUTOACK
					: self::AMQP_NONE
			);

			return $this;
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException|ObjectNotFoundException
		 * @return AMQPIncomingMessage
		**/
		public function basicGet($queue, $autoAck = true)
		{
			echo "\nchannel: basicGet";
			try {
				$obj = $this->lookupQueue($queue);
				$message = $obj->get(
					($autoAck === true)
						? AMQP_AUTOACK
						: self::AMQP_NONE
				);
			} catch (AMQPQueueException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);

			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if (!$message)
				throw new ObjectNotFoundException(
					"AMQP queue with name '{$queue}' is empty"
				);

			return AMQPPeclIncomingMessageAdapter::convert($message);
		}

		/**
		 * @throws AMQPServerExchangeException|AMQPServerConnectionException
		 * @param string $exchange
		 * @param string $routingKey
		 * @param AMQPOutgoingMessage $msg
		 * @return AMQPPeclChannel
		 */
		public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		) {
			echo "\nchannel: basicPublish";
			try {
				$obj = $this->lookupExchange($exchange);

				$result = $obj->publish(
					$msg->getBody(),
					$routingKey,
					$msg->getBitmask(new AMQPPeclOutgoingMessageBitmask()),
					$msg->getProperties()
				);
			} catch (AMQPExchangeException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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

		/**
		 * @param int $prefetchSize
		 * @param int $prefetchCount
		 * @return AMQPPeclChannel
		 */
		public function basicQos($prefetchSize, $prefetchCount)
		{
			echo "\nchannel: basicQos";
			try {
				$result = $this->getChannelLink()->qos(
					$prefetchSize,
					$prefetchCount
				);
			} catch (AMQPExchangeException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @param string $destinationName
		 * @param string $sourceName
		 * @param string $routingKey
		 * @return AMQPPeclChannel
		 */
		public function exchangeBind($destinationName, $sourceName, $routingKey)
		{
			echo "\nchannel: exchangeBind";
			try {
				$obj = $this->lookupExchange($destinationName);
				
				$result = $obj->bind(
					$sourceName,
					$routingKey
				);
			} catch (AMQPExchangeException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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

		public function exchangeUnbind($destinationName, $sourceName, $routingKey)
		{
			throw new UnimplementedFeatureException();
		}

		/**
		 * @throws AMQPServerException|AMQPServerConnectionException
		 * @param string $name
		 * @param AMQPExchangeConfig $conf
		 * @return AMQPPeclChannel
		 */
		public function exchangeDeclare($name, AMQPExchangeConfig $conf)
		{
			echo "\nchannel: exchangeDeclare";
			$this->checkConnection();

			if (!$conf->getType() instanceof AMQPExchangeType)
				throw new WrongArgumentException(
					"AMQP exchange type is not set"
				);

			try {
				$this->exchangeList[$name] =
					new AMQPExchange($this->getChannelLink());

				$obj = $this->exchangeList[$name];

                $obj->setName($name);
                $obj->setType($conf->getType()->getName());
                $obj->setFlags(
					$conf->getBitmask(new AMQPPeclExchangeBitmask())
				);
				$obj->setArguments($conf->getArguments());

				$result = $obj->declare();
			} catch (AMQPExchangeException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
			$name, $ifUnused = false
		) {
			echo "\nchannel: exchangeDelete";
			$bitmask = self::AMQP_NONE;

			if ($ifUnused)
				$bitmask = $bitmask | AMQP_IFUNUSED;

			try {
				$obj = $this->lookupExchange($name);
				$result = $obj->delete($name, $bitmask);
			} catch (AMQPExchangeException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
			echo "\nchannel: queueBind";
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->bind($exchange, $routingKey);
			} catch (AMQPQueueException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
			echo "\nchannel: queueDeclare";
			$this->checkConnection();

			try {

				if (isset($this->queueList[$name]))
					unset($this->queueList[$name]);
				
				$this->queueList[$name] =
					new AMQPQueue($this->getChannelLink());

				$obj = $this->queueList[$name];
				$obj->setName($name);
				$obj->setFlags(
					$conf->getBitmask(new AMQPPeclQueueBitmask())
				);
				$obj->setArguments($conf->getArguments());
				
				$result = $obj->declare();
			} catch (AMQPQueueException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
			echo "\nchannel: queueDelete";
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->delete();
			} catch (AMQPQueueException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
			echo "\nchannel: queuePurge";
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->purge();
			} catch (AMQPQueueException $e) {
				$this->clearConnection();

				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
			echo "\nchannel: queueUnbind";
			try {
				$obj = $this->lookupQueue($name);
				$result = $obj->unbind($exchange, $routingKey);
			} catch (AMQPQueueException $e) {
				$this->clearConnection();
				
				throw new AMQPServerException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			} catch (AMQPConnectionException $e) {
				$this->clearConnection();

				throw new AMQPServerConnectionException(
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
		 * @throws AMQPServerConnectionException
		 * @return AMQPExchange
		**/
		protected function lookupExchange($name)
		{
			$this->checkConnection();

			if (!isset($this->exchangeList[$name])) {
				$this->exchangeList[$name] =
					new AMQPExchange($this->getChannelLink());
				$this->exchangeList[$name]->setName($name);
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
				$this->queueList[$name] = new AMQPQueue($this->getChannelLink());
				if ($name != self::NIL)
						$this->queueList[$name]->setName($name);
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

		protected function clearConnection()
		{
			unset($this->link);
			$this->link = null;

			$this->exchangeList = array();
			$this->queueList = array();

			return $this;
		}

		protected function getChannelLink()
		{
			if (!$this->link) {
				echo "\nchannel: restart pecl-channel";
				$this->link = new AMQPChannel(
					$this->getTransport()->getLink()
				);
			}

			return $this->link;
		}

		protected function checkConnection()
		{
			return $this;
		}
	}
?>