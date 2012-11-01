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
	 * AMQP stands for Advanced Message Queue Protocol, which is
	 * an open standard middleware layer for message routing and queuing.
	**/
	namespace Onphp;

	interface AMQPInterface
	{
		/**
		 * @return \Onphp\AMQPInterface
		**/
		public function connect();

		/**
		 * @return \Onphp\AMQPInterface
		**/
		public function disconnect();
		
		/**
		 * @return \Onphp\AMQPInterface
		**/
		public function reconnect();

		/**
		 * @return boolean
		**/
		public function isConnected();

		/**
		 * @return \Onphp\AMQPInterface
		**/
		public function getLink();


		/**
		 * @param integer $id
		 * @throws WrongArgumentException
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function createChannel($id);

		/**
		 * @throws MissingElementException
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function getChannel($id);


		/**
		 * @return array
		**/
		public function getChannelList();

		/**
		 * @param integer $id
		 * @throws MissingElementException
		 * @return \Onphp\AMQPChannelInterface
		**/
		public function dropChannel($id);


		/**
		 * @return \Onphp\AMQPCredentials
		 */
		public function getCredentials();


		/**
		 * @return bool
		 */
		public function isAlive();


		/**
		 * @param bool $alive
		 * @return \Onphp\AMQPInterface
		 */
		//public function setAlive($alive);
	}
?>