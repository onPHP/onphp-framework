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

	namespace Onphp;

	final class TextFileQueueBrowser implements MessageQueueBrowser
	{
		private $queue = null;
		
		/**
		 * @return \Onphp\TextFileQueueBrowser
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\TextFileQueueBrowser
		**/
		public function setQueue(MessageQueue $queue)
		{
			$this->queue = $queue;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\MessageQueue
		**/
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