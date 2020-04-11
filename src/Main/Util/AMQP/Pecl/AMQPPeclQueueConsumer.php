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

namespace OnPHP\Main\Util\AMQP\Pecl;

use OnPHP\Main\Util\AMQP\AMQPDefaultConsumer;
use OnPHP\Main\Util\AMQP\AMQPIncomingMessage;

abstract class AMQPPeclQueueConsumer extends AMQPDefaultConsumer
{
	protected $cancel = false;
	protected $count = 0;
	protected $limit = 0;

	/**
	 * @param $cancel - type
	 * @return AMQPPeclQueueConsumer
	 */
	public function setCancel($cancel)
	{
		$this->cancel = ($cancel === true);
		return $this;
	}

	/**
	 * @param int $limit
	 * @return AMQPPeclQueueConsumer
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->count;
	}

	public function handlePeclDelivery(\AMQPEnvelope $delivery, \AMQPQueue $queue = null)
	{
		$this->count++;

		if ($this->limit && $this->count >= $this->limit)
			$this->setCancel(true);

		return $this->handleDelivery(
			AMQPPeclIncomingMessageAdapter::convert($delivery)
		);
	}

	public function handleDelivery(AMQPIncomingMessage $delivery)
	{
		if ($this->cancel) {
			$this->handleCancelOk('');
			return false;
		}
	}
}
?>