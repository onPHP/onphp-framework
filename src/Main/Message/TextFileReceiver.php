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
use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Exception\IOException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Main\Message\Specification\Message;
use OnPHP\Main\Message\Specification\MessageQueue;
use OnPHP\Main\Message\Specification\MessageQueueReceiver;
use OnPHP\Main\Util\IO\FileInputStream;
use OnPHP\Main\Util\IO\InputStream;

final class TextFileReceiver implements MessageQueueReceiver
{
	private ?MessageQueue $queue = null;
	private ?FileInputStream $stream = null;

	/**
	 * @return static
	 */
	public static function create(): TextFileReceiver
	{
		return new self;
	}

	/**
	 * @param MessageQueue $queue
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function setQueue(MessageQueue $queue): TextFileReceiver
	{
		Assert::isInstance($queue, TextFileQueue::class);

		$this->queue = $queue;

		return $this;
	}

	/**
	 * @return MessageQueue|null
	 */
	public function getQueue(): ?MessageQueue
	{
		return $this->queue;
	}

	/**
	 * @param int|null $uTimeout
	 * @return Message|null
	 * @throws WrongArgumentException
	 * @throws WrongStateException
	 * @throws IOException
	 */
	public function receive(int $uTimeout = null): ?Message
	{
		if (!$this->queue instanceof MessageQueue) {
			throw new WrongStateException('you must set the queue first');
		}

		if ($uTimeout && $this->getStream()->isEof()) {
			usleep($uTimeout);
		}

		$string = $this->getStream()->readString();

		if (!$string && $this->getStream()->isEof()) {
			return null;
		}

		if (($streamOffset = $this->getStream()->getOffset()) !== false) {
			$this->getQueue()->setOffset($streamOffset);
		}

		$string = rtrim($string, PHP_EOL);
		$chunks = preg_split("/\t/", $string, 2);

		$time = $chunks[0] ?? null;
		$text = $chunks[1] ?? null;

		Assert::isNotNull($time);

		$result = TextMessage::create(Timestamp::create($time))
			->setText($text);

		return $result;
	}

	/**
	 * @return FileInputStream|null
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function getStream(): ?FileInputStream
	{
		if (!$this->stream instanceof InputStream) {
			Assert::isNotNull($this->queue->getFileName());

			$this->stream =
				FileInputStream::create(
					$this->queue->getFileName()
				)->seek($this->queue->getOffset());
		}

		return $this->stream;
	}
}