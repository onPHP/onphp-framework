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
abstract class InputStream
{
	/**
	 * reads a maximum of $length bytes
	 * 
	 * returns null on eof or if length == 0.
	 * Otherwise MUST return at least one byte
	 * or throw IOException
	 * 
	 * NOTE: if length is too large to read all data at once and eof has
	 * not been reached, it MUST BLOCK until all data is read or eof is
	 * reached or throw IOException.
	 * 
	 * It is abnormal state. Maybe you should use some kind of
	 * non-blocking channels instead?
	 *
	 * @param int $length
	 * @return string|null
	 */
	abstract public function read(int $length): ?string;

	/**
	 * @return bool
	 */
	abstract public function isEof(): bool;

	/**
	 * @return static
	 */
	public function mark(): InputStream
	{
		/* nop */

		return $this;
	}

	/**
	 * @return bool
	 */
	public function markSupported(): bool
	{
		return false;
	}

	/**
	 * @return static
	 * @throws IOException
	 */
	public function reset(): InputStream
	{
		throw new IOException(
			'mark has been invalidated'
		);
	}

	/**
	 * @param int $count
	 * @return int
	 */
	public function skip(int $count): int
	{
		return mb_strlen($this->read($count));
	}

	/**
	 * @return int
	 */
	public function available(): int
	{
		return 0;
	}

	/**
	 * @return static
	 */
	public function close(): InputStream
	{
		/* nop */

		return $this;
	}
}