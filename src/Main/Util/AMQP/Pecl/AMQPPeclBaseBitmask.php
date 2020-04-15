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

use OnPHP\Main\Util\AMQP\AMQPBitmaskResolver;
use OnPHP\Core\Exception\UnimplementedFeatureException;

abstract class AMQPPeclBaseBitmask implements AMQPBitmaskResolver
{
	public function getBitmask($config)
	{
		$bitmask = 0;

		if ($config->getPassive())
			$bitmask = $bitmask | AMQP_PASSIVE;

		if ($config->getDurable())
			$bitmask = $bitmask | AMQP_DURABLE;

		if ($config->getAutodelete())
			$bitmask = $bitmask | AMQP_AUTODELETE;

		if ($config->getNowait())
			throw new UnimplementedFeatureException();

		return $bitmask;
	}
}
?>