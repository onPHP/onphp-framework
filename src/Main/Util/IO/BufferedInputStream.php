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

/**
 * @ingroup Utils
**/
final class BufferedInputStream extends InputStream
{
	private int $runAheadBytes = 0;

	private InputStream $in;
	private bool $closed = false;

	private $buffer			= null;
	private $bufferLength	= 0;

	private $position		= 0;
	private $markPosition	= null;

	public function __construct(InputStream $in)
	{
		$this->in = $in;
	}

	/**
	 * @param InputStream $in
	 * @return static
	 */
	public static function create(InputStream $in): BufferedInputStream
	{
		return new self($in);
	}

	/**
	 * @return static
	 */
	public function close(): BufferedInputStream
	{
		$this->closed = true;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEof(): bool
	{
		return $this->in->isEof();
	}

	/**
	 * @return bool
	 */
	public function markSupported(): bool
	{
		return true;
	}

	/**
	 * @return static
	 */
	public function mark(): BufferedInputStream
	{
		$this->markPosition = $this->position;

		return $this;
	}

	/**
	 * @return static
	 */
	public function reset(): BufferedInputStream
	{
		$this->position = $this->markPosition;

		return $this;
	}

	/**
	 * @return int
	 */
	public function available(): int
	{
		if ($this->closed) {
			return 0;
		}

		return ($this->bufferLength - $this->position);
	}

	/**
	 * @param int $runAheadBytes
	 * @return static
	 */
	public function setRunAheadBytes(int $runAheadBytes): BufferedInputStream
	{
		$this->runAheadBytes = $runAheadBytes;

		return $this;
	}

	/**
	 * @param int $length
	 * @return string|null
	 */
	public function read(int $length): ?string
	{
		if ($this->closed)
			return null;

		$remainingCount = $length;
		$availableCount = $this->available();

		if ($remainingCount <= $availableCount)
			$readFromBuffer = $length;
		else
			$readFromBuffer = $availableCount;

		$result = null;

		if ($readFromBuffer > 0) {
			$result = substr(
				$this->buffer, $this->position, $readFromBuffer
			);

			$this->position += $readFromBuffer;
			$remainingCount -= $readFromBuffer;
		}

		if ($remainingCount > 0) {

			$readAtOnce = ($remainingCount < $this->runAheadBytes)
				? $this->runAheadBytes
				: $remainingCount;

			$readBytes = $this->in->read($readAtOnce);
			$readBytesLength = strlen($readBytes);

			if ($readBytesLength > 0) {
				$this->buffer .= $readBytes;
				$this->bufferLength += $readBytesLength;

				if ($readBytesLength <= $remainingCount) {
					$this->position += $readBytesLength;
					$result .= $readBytes;
				} else {
					$this->position += $remainingCount;
					$result .= substr($readBytes, 0, $remainingCount);
				}
			}
		}

		return $result;
	}
}