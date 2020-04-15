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

namespace OnPHP\Main\Util\AMQP;

/**
 * @see http://www.rabbitmq.com/amqp-0-9-1-quickref.html#queue.declare
**/
final class AMQPQueueConfig extends AMQPBaseConfig
{
	protected $exclusive = false;

	/**
	 * @return AMQPQueueConfig
	**/
	public static function create()
	{
		return new self();
	}

	public function getExclusive()
	{
		return $this->exclusive;
	}

	/**
	 * @param boolean $exclusive
	 * @return AMQPQueueConfig
	**/
	public function setExclusive($exclusive)
	{
		$this->exclusive = $exclusive === false;

		return $this;
	}

}
?>