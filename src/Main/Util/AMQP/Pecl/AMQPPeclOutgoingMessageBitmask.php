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

namespace OnPHP\Main\Util\AMQP\Pecl;

use OnPHP\Core\Base\Assert;
use OnPHP\Main\Util\AMQP\AMQPBitmaskResolver;
use OnPHP\Main\Util\AMQP\AMQPOutgoingMessage;

/**
 * @see http://www.php.net/manual/en/amqp.constants.php
**/
final class AMQPPeclOutgoingMessageBitmask implements AMQPBitmaskResolver
{
	public function getBitmask($config)
	{
		Assert::isInstance($config, AMQPOutgoingMessage::class);

		$bitmask = 0;

		if ($config->getMandatory())
			$bitmask = $bitmask | AMQP_MANDATORY;

		if ($config->getImmediate())
			$bitmask = $bitmask | AMQP_IMMEDIATE;

		return $bitmask;
	}
}
?>