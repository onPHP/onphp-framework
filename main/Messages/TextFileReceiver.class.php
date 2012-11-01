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

	final class TextFileReceiver implements MessageQueueReceiver
	{
		private $queue	= null;
		private $stream	= null;
		
		/**
		 * @return \Onphp\TextFileReceiver
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\TextFileReceiver
		**/
		public function setQueue(MessageQueue $queue)
		{
			Assert::isInstance($queue, '\Onphp\TextFileQueue');
			
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
		
		/**
		 * @return \Onphp\Message
		**/
		public function receive($uTimeout = null)
		{
			if (!$this->queue)
				throw new WrongStateException('you must set the queue first');
			
			if ($uTimeout && $this->getStream()->isEof())
				usleep($uTimeout);
			
			$string = $this->getStream()->readString();
			
			if (!$string && $this->getStream()->isEof())
				return null;
			
			$this->getQueue()->setOffset($this->getStream()->getOffset());
			
			$string = rtrim($string, PHP_EOL);
			
			$chunks = preg_split("/\t/", $string, 2);
			
			$time = isset($chunks[0]) ? $chunks[0] : null;
			$text = isset($chunks[1]) ? $chunks[1] : null;
			
			Assert::isNotNull($time);
			
			$result = TextMessage::create(Timestamp::create($time))->
				setText($text);
			
			return $result;
		}
		
		/**
		 * @return \Onphp\FileInputStream
		**/
		private function getStream()
		{
			if (!$this->stream) {
				Assert::isNotNull($this->queue->getFileName());
				
				$this->stream = FileInputStream::create(
					$this->queue->getFileName()
				)->
					seek($this->queue->getOffset());
			}
			
			return $this->stream;
		}
	}
?>