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

	class SocketOutputStream extends OutputStream
	{
		const WRITE_ATTEMPTS = 42; // should be enough for everyone (C)
		
		private $socket = null;
		
		public function __construct(Socket $socket)
		{
			$this->socket = $socket;
		}
		
		/**
		 * @return SocketOutputStream
		**/
		public function write($buffer)
		{
			if ($buffer === null)
				return $this;
			
			$totalBytes = strlen($buffer);
			
			try {
				$writtenBytes = $this->socket->write($buffer);
				
				$i = 0;
				
				while (
					$writtenBytes < $totalBytes
					&& ($i < self::WRITE_ATTEMPTS)
				) {
					// 0.01s sleep insurance if socket timeouts is broken
					usleep(10000);
					
					$buffer = substr($buffer, $writtenBytes);
					$writtenBytes += $this->socket->write($buffer);
					
					++$i;
				}
			} catch (NetworkException $e) {
				throw new IOException($e->getMessage());
			}
			
			if ($i == self::WRITE_ATTEMPTS)
				throw new IOException(
					'timeout while trying to write into socket'
				);
			
			return $this;
		}
	}
?>