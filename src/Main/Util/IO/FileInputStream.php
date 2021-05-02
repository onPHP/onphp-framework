<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\IO;

use OnPHP\Core\Exception\IOException;
use OnPHP\Core\Exception\BaseException;

/**
 * @ingroup Utils
**/
final class FileInputStream extends InputStream
{
	/**
	 * @var resource
	 */
	private $fd;
	private $mark = null;

	/**
	 * @param resource $nameOrFd
	 * @throws IOException
	 */
	public function __construct($nameOrFd)
	{
		if (is_resource($nameOrFd)) {
			if (get_resource_type($nameOrFd) !== 'stream') {
				throw new IOException('not a file resource');
			}

			$this->fd = $nameOrFd;
		} else {
			try {
				$this->fd = fopen($nameOrFd, 'rb');
			} catch (BaseException $e) {
				throw new IOException($e->getMessage());
			}
		}
	}

	public function __destruct()
	{
		try {
			$this->close();
		} catch (BaseException $e) {
			// boo.
		}
	}

	/**
	 * @param resource $nameOrFd
	 * @return static
	 * @throws IOException
	 */
	public static function create($nameOrFd): FileInputStream
	{
		return new self($nameOrFd);
	}

	/**
	 * @return bool
	 */
	public function isEof(): bool
	{
		return feof($this->fd);
	}

	/**
	 * @return static
	 */
	public function mark(): FileInputStream
	{
		$this->mark = $this->getOffset();

		return $this;
	}

	/**
	 * @return false|int
	 */
	public function getOffset()
	{
		return ftell($this->fd);
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
	 * @throws IOException
	 */
	public function reset(): FileInputStream
	{
		return $this->seek($this->mark);
	}

	/**
	 * @param int $offset
	 * @return static
	 * @throws IOException
	 */
	public function seek(int $offset): FileInputStream
	{
		if (fseek($this->fd, $offset) < 0) {
			throw new IOException('mark has been invalidated');
		}

		return $this;
	}

	/**
	 * @return static
	 * @throws IOException
	 */
	public function close(): FileInputStream
	{
		if (!fclose($this->fd)) {
			throw new IOException('failed to close the file');
		}

		return $this;
	}

	/**
	 * @param int $length
	 * @return string|null
	 * @throws IOException
	 */
	public function read(int $length): ?string
	{
		return $this->realRead($length);
	}

	/**
	 * @param int|null $length
	 * @return string|null
	 * @throws IOException
	 */
	public function readString(int $length = null): ?string
	{
		return $this->realRead($length, true);
	}

	/**
	 * @param int $length
	 * @param bool $string
	 * @return string|null
	 * @throws IOException
	 */
	public function realRead(int $length, bool $string = false): ?string
	{
		$result = $string
			? (
				$length === null
				? fgets($this->fd)
				: fgets($this->fd, $length)
			)
			: fread($this->fd, $length);

		if ($string && $result === false && feof($this->fd)) {
			$result = null; // fgets returns false on eof
		}

		if ($result === false) {
			throw new IOException('failed to read from file');
		}

		if ($result === '') {
			$result = null; // eof
		}

		return $result;
	}
}