<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Message\Specification;

interface MessageQueueReceiver
{
	/**
	 * @return Message
	**/
	public function receive($uTimeout = null);

	/**
	 * @return MessageQueue
	**/
	public function getQueue();
}
?>