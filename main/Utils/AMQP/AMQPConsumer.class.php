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

	interface AMQPConsumer
	{
		/**
		 * @return AMQPChannelInterface
		**/
		public function getChannel();

		/**
		 * Called when a delivery appears for this consumer.
		 * @param AMQPIncomingMessage $delivery
		 * @return void
		**/
		public function handleDelivery(AMQPIncomingMessage $delivery);

		/**
		 * Called when the consumer is first registered by a call
		 * to {@link Channel#basicConsume}.
		 *
		 * @param consumerTag the defined consumerTag
		 * @return void
		**/
		public function handleConsumeOk($consumerTag);

		/**
		 * Called when the consumer is deregistered by a call
		 * to {@link Channel#basicCancel}.
		 *
		 * @param consumerTag the defined consumerTag
		 * @return void
		**/
		public function handleCancelOk($consumerTag);

		/**
		 * Called when the consumer is changed tag
		 *
		 * @param string $fromTag
		 * @param string $toTag
		 * @return void
		**/
		public function handleChangeConsumerTag($fromTag, $toTag);

		/**
		 * @return AMQPConsumer
		**/
		public function setQueueName($name);

		/**
		 * @return string
		**/
		public function getQueueName();

		/**
		 * @return AMQPConsumer
		**/
		public function setAutoAcknowledge($boolean);

		/**
		 * @return boolean
		**/
		public function isAutoAcknowledge();

		/**
		 * @return AMQPConsumer
		**/
		public function setConsumerTag($consumerTag);

		/**
		 * @return string
		**/
		public function getConsumerTag();

		/**
		 * @return AMQPIncomingMessage
		**/
		public function getNextDelivery();
	}
?>