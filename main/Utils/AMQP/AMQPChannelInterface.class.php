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

	namespace Onphp;

	interface AMQPChannelInterface
	{
		/**
		 * @return true
		**/
		public function isOpen();

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function open();

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function close();

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeDeclare(
			$name, AMQPExchangeConfig $conf
		);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeDelete(
			$name, $ifUnused = false
		);

		/**
		 * @see http://www.rabbitmq.com/blog/2010/10/19/exchange-to-exchange-bindings/
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeBind($destinationName, $sourceName, $routingKey);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function exchangeUnbind($destinationName, $sourceName, $routingKey);

		/**
		 * @return integer - the message count in queue
		**/
		public function queueDeclare($name, AMQPQueueConfig $conf);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queueBind($name, $exchange, $routingKey);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queueUnbind($name, $exchange, $routingKey);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queuePurge($name);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function queueDelete($name);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicQos($prefetchSize, $prefetchCount);

		/**
		 * @return \Onphp\AMQPIncomingMessage
		**/
		public function basicGet($queue, $autoAck = true);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicAck($deliveryTag, $multiple = false);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicConsume($queue, $autoAck, AMQPConsumer $callback);

		/**
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function basicCancel($consumerTag);
	}
?>