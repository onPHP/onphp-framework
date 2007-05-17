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

	class SocketInputStream extends InputStream
	{
		const READ_ATTEMPTS	= 42; // it's should be enough
		
		private $socket = null;
		
		public function __construct(Socket $socket)
		{
			$this->socket = $socket;
		}
		
		public function read($length)
		{
			if ($length == 0)
				return null;
			
			if ($this->eof)
				return false;
			
			try {
				$result = $this->socket->read($length);
				
				$i = 0;
				
				while (
					!$result && $this->socket->isTimedOut()
					&& $i < self::READ_ATTEMPTS
				) {
					// 0.01s sleep insurance if socket timeouts is broken
					usleep(10000);
				
					$result .= $this->socket->read($length);
				
					++$i;
				}
				
			} catch (NetworkException $e) {
				throw new IOException($e->getMessage());
			}
			
			if ($i == self::READ_ATTEMPTS)
				throw new IOException(
					'timeout while trying to read socket'
				);
			
			if (!$result) {
				$this->eof = true;
					
				return false;
			}
			
			return $result;
		}
	}
?>