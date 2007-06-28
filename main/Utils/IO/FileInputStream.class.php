<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	class FileInputStream extends InputStream
	{
		private $name	= null;
		private $fd		= null;
		
		private $mark	= null;
		
		public function __construct($name)
		{
			if (!is_file($name) || !is_readable($name))
				throw new FileNotFoundException($name);
			
			try {
				$this->fd = fopen($name, 'rb');
			} catch (BaseException $e) {
				throw new IOException($e->getMessage());
			}
			
			$this->name = $name;
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
		public static function create($name)
		{
			return new self($name);
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
			$this->mark = ftell($this->fd);
			
			return $this;
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
			if (fseek($this->fd, $this->mark) < 0)
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
			$result = fread($this->fd, $length);
			
			if ($result === false)
				throw new IOException('failer to read from file');
			
			if ($result === '')
				$result = null; // eof
			
			return $result;
		}
	}
?>