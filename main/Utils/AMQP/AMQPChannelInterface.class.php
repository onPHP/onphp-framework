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

	interface AMQPChannelInterface
	{
		/**
		 * @return true
		**/
		public function isOpen();

		/**
		 * @return AMQPChannelInterface
		**/
		public function open();

		/**
		 * @return AMQPChannelInterface
		**/
		public function close();

		/**
		 * @return AMQPChannelInterface
		**/
		public function exchangeDeclare(
			$name, AMQPExchangeConfig $conf
		);

		/**
		 * @return AMQPChannelInterface
		**/
		public function exchangeDelete(
			$name, $ifUnused = false, $ifEmpty = false
		);

		/**
		 * @return AMQPChannelInterface
		**/
		public function exchangeBind($name, $queue, $routingKey);

		/**
		 * @return AMQPChannelInterface
		**/
		public function exchangeUnbind($name, $queue, $routingKey);

		/**
		 * @see http://www.rabbitmq.com/blog/2010/10/19/exchange-to-exchange-bindings/
		**/
		public function exchangeToExchangeBind(
			$destination, $source, $routingKey
		);

		public function exchangeToExchangeUnbind(
			$destination, $source, $routingKey
		);

		/**
		 * @return integer - the message count in queue
		**/
		public function queueDeclare($name, AMQPQueueConfig $conf);

		/**
		 * @return AMQPChannelInterface
		**/
		public function queueBind($name, $exchange, $routingKey);

		/**
		 * @return AMQPChannelInterface
		**/
		public function queueUnbind($name, $exchange, $routingKey);

		/**
		 * @return AMQPChannelInterface
		**/
		public function queuePurge($name);

		/**
		 * @return AMQPChannelInterface
		**/
		public function queueDelete($name);

		/**
		 * @return AMQPChannelInterface
		**/
		public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		);

		/**
		 * @return AMQPChannelInterface
		**/
		public function basicQos($prefetchSize, $prefetchCount);

		/**
		 * @return AMQPIncomingMessage
		**/
		public function basicGet($queue, $noAck = true);

		/**
		 * @return AMQPChannelInterface
		**/
		public function basicAck($deliveryTag, $multiple = false);

		/**
		 * @return AMQPChannelInterface
		**/
		public function basicConsume($queue, /*Consumer*/ $callback);

		/**
		 * @return AMQPChannelInterface
		**/
		public function basicCancel($consumerTag);
	}
?>