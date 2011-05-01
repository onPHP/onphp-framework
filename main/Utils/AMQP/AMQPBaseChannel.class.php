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
	 * Base class modelling an AMQ channel
	**/
	abstract class AMQPBaseChannel
	{
		protected $id = null;

		/**
		 * @var AMQP
		**/
		protected $transport = null;

		abstract public function isOpen();
		abstract public function open();
		abstract public function close();

		/**
		 * @return boolean
		**/
		abstract public function exchangeDeclare(
			$name, AMQPExchangeConfig $conf
		);

		/**
		 * @return boolean
		**/
		abstract public function exchangeDelete(
			$name, $ifUnused = false, $ifEmpty = false
		);
		
		/**
		 * @see http://www.rabbitmq.com/blog/2010/10/19/exchange-to-exchange-bindings/
		**/
		abstract public function exchangeToExchangeBind(
			$destination, $source, $routingKey
		);

		abstract public function exchangeToExchangeUnbind(
			$destination, $source, $routingKey
		);

		/**
		 * @return integer - the message count in queue
		**/
		abstract public function queueDeclare($name, AMQPQueueConfig $conf);

		/**
		 * @return boolean
		**/
		abstract public function queueBind($name, $exchange, $routingKey);

		/**
		 * @return boolean
		**/
		abstract public function queueUnbind($name, $exchange, $routingKey);

		/**
		 * @return boolean
		**/
		abstract public function queuePurge($name);

		/**
		 * @return boolean
		**/
		abstract public function queueDelete($name);

		abstract public function basicPublish(
			$exchange, $routingKey, AMQPOutgoingMessage $msg
		);
		
		abstract public function basicQos($prefetchSize, $prefetchCount);
		abstract public function basicGet($queue, $noAck = true);
		abstract public function basicAck($deliveryTag, $multiple = false);
		abstract public function basicConsume($queue, /*Consumer*/ $callback);
		abstract public function basicCancel($consumerTag);

		public function __construct($id, AMQP $transport)
		{
			$this->id = $id;
			$this->transport = $transport;
		}

		public function getTransport()
		{
			return $this->transport;
		}

		public function getId()
		{
			return $this->id;
		}
	}
?>