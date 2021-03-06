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

namespace OnPHP\Main\Message;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Main\Message\Specification\Message;
use OnPHP\Main\Message\Specification\MessageQueue;
use OnPHP\Main\Message\Specification\MessageQueueSender;
use OnPHP\Main\Util\IO\FileOutputStream;

final class TextFileSender implements MessageQueueSender
{
	private $queue	= null;
	private $stream	= null;

	public static function create()
	{
		return new self;
	}

	public function setQueue(MessageQueue $queue)
	{
		Assert::isInstance($queue, TextFileQueue::class);

		$this->queue = $queue;

		return $this;
	}

	/**
	 * @return MessageQueue
	**/
	public function getQueue()
	{
		return $this->queue;
	}

	public function send(Message $message)
	{
		if (!$this->queue)
			throw new WrongStateException('you must set the queue first');

		Assert::isInstance($message, TextMessage::class);

		$this->getStream()->write(
			$message->getTimestamp()->toString()."\t"
			.str_replace(PHP_EOL, ' ', $message->getText()).PHP_EOL
		);
	}

	private function getStream()
	{
		if (!$this->stream) {
			Assert::isNotNull($this->queue->getFileName());

			$this->stream = FileOutputStream::create(
				$this->queue->getFileName(), true
			);
		}

		return $this->stream;
	}
}
?>
