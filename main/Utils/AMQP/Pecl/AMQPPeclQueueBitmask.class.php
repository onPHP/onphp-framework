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
	 * @see http://www.php.net/manual/en/amqp.constants.php
	**/
	namespace Onphp;

	final class AMQPPeclQueueBitmask extends AMQPPeclBaseBitmask
	{
		public function getBitmask($config)
		{
			Assert::isInstance($config, '\Onphp\AMQPQueueConfig');

			$bitmask = parent::getBitmask($config);

			if ($config->getExclusive())
				$bitmask = $bitmask | AMQP_EXCLUSIVE;

			return $bitmask;
		}
	}
?>