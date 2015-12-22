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
	final class AMQPPeclExchangeBitmask extends AMQPPeclBaseBitmask
	{
		public function getBitmask($config)
		{
			Assert::isInstance($config, 'AMQPExchangeConfig');

			$bitmask = parent::getBitmask($config);

			if ($config->getInternal())
				$bitmask = $bitmask | AMQP_INTERNAL;
			
			return $bitmask;
		}
	}
?>