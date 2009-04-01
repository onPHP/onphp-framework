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
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class FileInputStream extends InputStream
	{
		private $fd		= null;
		
		private $mark	= null;
		
		public function __construct($nameOrFd)
		{
			if (is_resource($nameOrFd)) {
				if (get_resource_type($nameOrFd) !== 'stream')
					throw new IOException('not a file resource');
				
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
		 * @return FileInputStream
		**/
		public static function create($nameOrFd)
		{
			return new self($nameOrFd);
		}
		
		public function isEof()
		{
			return feof($this->fd);
		}
		
		/**
		 * @return FileInputStream
		**/
		public function mark()
		{
			$this->mark = $this->getOffset();
			
			return $this;
		}
		
		public function getOffset()
		{
			return ftell($this->fd);
		}
		
		public function markSupported()
		{
			return true;
		}
		
		/**
		 * @return FileInputStream
		**/
		public function reset()
		{
			return $this->seek($this->mark);
		}
		
		/**
		 * @return FileInputStream
		**/
		public function seek($offset)
		{
			if (fseek($this->fd, $offset) < 0)
				throw new IOException(
					'mark has been invalidated'
				);
			
			return $this;
		}
		
		/**
		 * @return FileInputStream
		**/
		public function close()
		{
			if (!fclose($this->fd))
				throw new IOException('failed to close the file');
			
			return $this;
		}

		public function read($length)
		{
			return $this->realRead($length);
		}

		public function readString($length = null)
		{
			return $this->realRead($length, true);
		}

		public function realRead($length, $string = false)
		{
			$result = $string
				? (
					$length === null
					? fgets($this->fd)
					: fgets($this->fd, $length)
				)
				: fread($this->fd, $length);

			if ($result === false)
				throw new IOException('failed to read from file');

			if ($result === '')
				$result = null; // eof

			return $result;
		}
	}
?>