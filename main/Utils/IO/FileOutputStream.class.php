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
/* $Id$ */

	class FileOutputStream extends OutputStream
	{
		private $name	= null;
		private $fd		= null;
		
		public function __construct($name, $append = false)
		{
			try {
				$this->fd = fopen($name, ($append ? 'a' : 'w').'b');
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
		 * @return FileOutputStream
		**/
		public static function create($name, $append = false)
		{
			return new self($name, $append);
		}
		
		/**
		 * @return FileOutputStream
		**/
		public function write($buffer)
		{
			if (!$this->fd || $buffer === null)
				return $this;
			
			try {
				$written = fwrite($this->fd, $buffer);
			} catch (BaseException $e) {
				throw new IOException($e->getMessage());
			}
			
			if (!$written || $written < strlen($buffer))
				throw new IOException('disk full and/or buffer too large?');
			
			return $this;
		}
		
		/**
		 * @return FileOutputStream
		**/
		public function close()
		{
			fclose($this->fd);
			
			$this->fd = null;
			
			return $this;
		}
	}
?>