<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Scheglov K.                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   Based on Net/Socket.php (C) PEAR: Stig Bakken <ssb@php.net>,          *
 *   Chuck Hagenbuch <chuck@horde.org>                                     *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	class SocketException extends BaseException {/*_*/}

	class Socket
	{
		const BLOCK_SIZE				= 1024;

		const NET_SOCKET_READ			= 1;
		const NET_SOCKET_WRITE			= 2;
		const NET_SOCKET_ERROR			= 3;
		
		const NET_SOCKET_DEFAULT_PORT 	= 80;

		private $blocking			= true;
		private $persistent 		= false;
		private $options			= array();

		private $socketFilePointer	= null;
		private $host				= null;

		private $port				= 80;
		private $lineLength			= 2048;
		private $timeout			= 30;

		private $errorNo			= 0;
		private $errorString		= ''; // not null!
		
		public static function create()
		{
			return new Socket();
		}
		
		public function setPort($port)
		{
			if (($port > 0) && ($port < 65536))
				$this->post = $port;
			else
				throw new SocketException(
					"invalid port number '{$port}' specified"
				);

			return $this;
		}

		public function setHost($host)
		{
			if (strspn($host, '.0123456789') == strlen($host) ||
					strstr($host, '/') !== false
			) {
				$this->host = $host;
			} else {
				try {
					$this->host = gethostbyname($host);
				} catch (BaseException $e) {
					throw new SocketException("can not resolve host '{$host}'");
				}
			}

			return $this;
		}

		public function setPersistent($persistent = false)
		{
			$this->persistent = ($persistent === true ? true : false);

			return $this;
		}

		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;
			
			return $this;
		}
		
		public function setOptions($options)
		{
			if (!is_array($options))
				throw new SocketException('wrong options format');

			$this->options = $options;
			
			return $this;
		}

		public function setBlocking($blocking = false)
		{
			$this->blocking = ($blocking === true ? true : false);

			return $this;
		}
		
		public function getErrorNumber()
		{
			return $this->errorNo;
		}
		
		public function getErrorString()
		{
			return $this->errorString;
		}

		public function connect()
		{
			$openFunction = $this->persistent ? 'pfsockopen' : 'fsockopen';			

			if ($this->options && function_exists('stream_context_create')) {
				$context = stream_context_create($this->options);
				$socketFilePointer =
					$openFunction(
						$this->host,
						$this->port,
						$this->errorNo,
						$this->errorString,
						$this->timeout,
						$context
					);
			} else
				$socketFilePointer =
					$openFunction(
						$this->host,
						$this->port,
						$this->errorNo,
						$this->errorString,
						$this->timeout
					);

			$this->socketFilePointer = &$socketFilePointer;

			socket_set_blocking($this->socketFilePointer, $this->blocking);

			return true;
		}

		public function disconnect()
		{
			try {
				fclose($this->socketFilePointer);
				$this->socketFilePointer = null;
			} catch (BaseException $e) {
				throw new SocketException("failed to close socket");
			}

			return $this;
		}
		
		public function isConnected()
		{
			return ($this->socketFilePointer === null ? false : true);
		}

		public function isBlocking()
		{
			return $this->blocking;
		}

		// aka setTimeout
		public function setSocketTimeout($seconds, $microseconds = null)
		{
			try {
				stream_set_timeout($this->socketFilePointer, $seconds, $microseconds);
			} catch (BaseException $e) {
				throw new SocketException("failed to set socket's timeout");
			}
	
			return $this;
		}

		public function getStatus()
		{
			return stream_get_meta_data($this->socketFilePointer);
		}

		public function gets($size)
		{
			try {
				$line = fgets($this->socketFilePointer, $size);
			} catch (BaseException $e) {
				throw new SocketException(
					"Get a specified line of data failure"
				);
			}

			return $line;
		}

		public function read($size)
		{
			try {
				$data = fread($this->socketFilePointer, $size);
			} catch (BaseException $e) {
				throw new SocketException(
					"Read a specified amount of data failure"
				);
			}

			return $data;
		}

		public function write($data)
		{
			return fwrite($this->socketFilePointer, $data);
		}

		public function writeLine($data)
		{
			try {
				fwrite($this->socketFilePointer, $data . "\r\n");
			} catch (BaseException $e) {
				throw new SocketException("Write line failure");
			}

			return $this;
		}

		public function isEOF()
		{
			return feof($this->socketFilePointer);
		}

		public function readByte()
		{
			try {
				$string = fread($this->socketFilePointer, 1);
			} catch (BaseException $e) {
				throw new SocketException("Read Byte failure");
			}

			return ord($string);
		}

		public function readWord()
		{
			try {
				$buffer = fread($this->socketFilePointer, 2);
			} catch (BaseException $e) {
				throw new SocketException("Read Word failure");
			}

			return (ord($buffer[0]) + (ord($buffer[1]) << 8));
		}

		public function readInt()
		{
			try {
				$buffer = fread($this->socketFilePointer, 4);
			} catch (BaseException $e) {
				throw new SocketException("Read Int failure");
			}

			return
				(
					ord($buffer[0]) +
					(ord($buffer[1]) << 8) +
					(ord($buffer[2]) << 16) +
					(ord($buffer[3]) << 24)
				);
		}

		public function readString()
		{
			try {
				$string = '';
				while (($char = fread($this->socketFilePointer, 1)) != "\x00")
					$string .= $char;
			} catch (BaseException $e) {
				throw new SocketException("Read String failure");
			}

			return $string;
		}

		public function readHost()
		{
			try {
				$buffer = fread($this->socketFilePointer, 4);
			} catch (BaseException $e) {
				throw new SocketException("Read Host address failure");
			}

			return
				sprintf(
					"%s.%s.%s.%s",
					ord($buffer[0]),
					ord($buffer[1]),
					ord($buffer[2]),
					ord($buffer[3])
				);
		}

		public function readLine()
		{
			try {
				$line = '';
				$timeout = time() + $this->timeout;

				while (
					!feof($this->socketFilePointer)
					&& (!$this->timeout || time() < $timeout)
				) {
					$line .= fgets($this->socketFilePointer, $this->lineLength);

					if (substr($line, -1) == "\n")
						return rtrim($line, "\r\n");
				}
			} catch (BaseException $e) {
				throw new SocketException("Read line failure");
			}

			return $line;
		}

		public function readAll()
		{
			try {
				$data = '';
				while (!feof($this->socketFilePointer))
					$data .= fread($this->socketFilePointer, $this->lineLength);
			} catch (BaseException $e) {
				throw new SocketException("Read all failure");
			}
			
			return $data;
		}

		public function select(
			$state, $secondsForTimeout, $microsecondsForTimeout = 0
		)
		{
			$read   = null;
			$write  = null;
			$except = null;

			try {
				if ($state & self::NET_SOCKET_READ)
					$read[] = $this->socketFilePointer;
				
				if ($state & self::NET_SOCKET_WRITE)
					$write[] = $this->socketFilePointer;
				
				if ($state & self::NET_SOCKET_ERROR)
					$except[] = $this->socketFilePointer;
				
				if (false ===
					($sr =
						stream_select(
							$read,
							$write,
							$except,
							$secondsForTimeout,
							$microsecondsForTimeout
						)
					)
				) {
					return false;
				}

				$result = 0;

				if (count($read))
					$result |= self::NET_SOCKET_READ;
				
				if (count($write))
					$result |= self::NET_SOCKET_WRITE;
				
				if (count($except))
					$result |= self::NET_SOCKET_ERROR;
				
			} catch (BaseException $e) {
				throw new SocketException("Select failure");
			}

			return $result;
		}
	}
?>