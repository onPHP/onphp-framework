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
	 * @see http://www.rabbitmq.com/amqp-0-9-1-quickref.html#exchange.declare
	**/
	namespace Onphp;

	final class AMQPExchangeConfig extends AMQPBaseConfig
	{
		protected $internal = null;

		/**
		 * @var \Onphp\AMQPExchangeType
		**/
		protected $type = null;

		/**
		 * @return \Onphp\AMQPExchangeConfig
		**/
		public static function create()
		{
			return new self();
		}

		/**
		 * @param \Onphp\AMQPExchangeType $type
		 * @return \Onphp\AMQPExchangeConfig
		**/
		public function setType(AMQPExchangeType $type)
		{
			$this->type = $type;

			return $this;
		}

		/**
		 * @return \Onphp\AMQPExchangeType
		**/
		public function getType()
		{
			return $this->type;
		}

		public function getInternal()
		{
			return $this->internal;
		}

		/**
		 * @param boolean $internal
		 * @return \Onphp\AMQPExchangeConfig
		**/
		public function setInternal($internal)
		{
			$this->internal = $internal;

			return $this;
		}		
	}
?>