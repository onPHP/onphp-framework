<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\IO;

use OnPHP\Core\Exception\IOException;

/**
 * @ingroup Utils
**/
final class BufferedReader extends Reader
{
	private $in				= null;
	private $closed			= false;

	private $buffer			= null;
	private $bufferLength	= 0;

	private $position		= 0;
	private $markPosition	= null;

	public function __construct(Reader $in)
	{
		$this->in = $in;
	}

	/**
	 * @return BufferedReader
	**/
	public static function create(Reader $in)
	{
		return new self($in);
	}

	/**
	 * @return BufferedReader
	**/
	public function close()
	{
		$this->closed = true;

		return $this;
	}

	public function isEof()
	{
		return $this->in->isEof();
	}

	public function markSupported()
	{
		return true;
	}

	/**
	 * @return BufferedReader
	**/
	public function mark()
	{
		$this->markPosition = $this->position;

		return $this;
	}

	/**
	 * @return BufferedReader
	**/
	public function reset()
	{
		$this->position = $this->markPosition;

		return $this;
	}

	public function available()
	{
		$this->ensureOpen();

		return ($this->bufferLength - $this->position);
	}

	public function read($count)
	{
		$this->ensureOpen();

		$remainingCount = $count;
		$availableCount = $this->available();

		if ($remainingCount <= $availableCount)
			$readFromBuffer = $count;
		else
			$readFromBuffer = $availableCount;

		$result = null;

		if ($readFromBuffer > 0) {
			$result = mb_substr(
				$this->buffer,
				$this->position,
				$readFromBuffer
			);

			$this->position += $readFromBuffer;
			$remainingCount -= $readFromBuffer;
		}

		if ($remainingCount > 0) {
			$remaining = $this->in->read($remainingCount);

			if ($this->markPosition !== null) {
				$this->buffer .= $remaining;
				$remainingLength = mb_strlen($remaining);

				$this->bufferLength += $remainingLength;
				$this->position += $remainingLength;
			}

			if ($remaining !== null)
				$result .= $remaining;
		}

		return $result;
	}

	/* void */ private function ensureOpen()
	{
		if ($this->closed)
			throw new IOException('stream has been closed');
	}
}
?>