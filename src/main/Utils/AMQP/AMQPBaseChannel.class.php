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
	abstract class AMQPBaseChannel implements AMQPChannelInterface
	{
		protected $id = null;

		/**
		 * @var AMQPInterface
		**/
		protected $transport = null;

		public function __construct($id, AMQPInterface $transport)
		{
			$this->id = $id;
			$this->transport = $transport;
		}

		public function __destruct()
		{
			if ($this->isOpen())
				$this->close();
		}

		public function getTransport()
		{
			return $this->transport;
		}

		public function getId()
		{
			return $this->id;
		}

		/**
		 * @throws AMQPServerConnectionException
		 * @return AMQPBaseChannel
		**/
		protected function checkConnection()
		{
			if (!$this->transport->getLink()->isConnected()) {
				throw new AMQPServerConnectionException(
					"No connection available"
				);
			}

			return $this;
		}
	}
?>