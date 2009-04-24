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
/* $Id$ */

	final class TextFileQueueBrowser implements MessageQueueBrowser
	{
		private $queue = null;

		public static function create()
		{
			return new self;
		}

		public function setQueue(MessageQueue $queue)
		{
			$this->queue = $queue;

			return $this;
		}

		/**
		 * @return MessageQueue
		 */
		public function getQueue()
		{
			return $this->queue;
		}

		public function getNextMessage()
		{
			throw new UnimplementedFeatureException;
		}
	}
?>
