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

	class Socket
	{
		const DEFAULT_TIMEOUT	= 1000; // milliseconds, 10^3
		
		const EAGAIN			= 11;	// timeout, try again
		
		private $socket		= null;
		private $connected	= false;
		
		private $host		= null;
		private $port		= null;
		
		private $inputStream	= null;
		private $outputStream	= null;
		
		private $closed			= false;
		private $inputShutdown	= false;
		private $outputShutdown	= false;
		
		/**
		 * timeout for read/write operations
		**/
		private $timeout	= null;
		
		public function __construct()
		{
			$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			
			if ($this->socket === false)
				throw new NetworkException(
					"socket creating failed: "
					.socket_strerror(socket_last_error())
				);
			
			$this->inputStream = new SocketInputStream($this);
			
			$this->outputStream = new SocketOutputStream($this);
		}
		
		public function __destruct()
		{
			if (!$this->closed) {
				try {
					$this->close();
				} catch (BaseException $e) {
					/* boo! */
				}
			}
		}
		
		/**
		 * @return Socket
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return Socket
		**/
		public function setHost($host)
		{
			Assert::isNull($this->host);
			
			$this->host = $host;
			
			return $this;
		}
		
		public function getHost()
		{
			return $this->host;
		}
		
		/**
		 * @return Socket
		**/
		public function setPort($port)
		{
			Assert::isNull($this->port);
			
			$this->port = $port;
			
			return $this;
		}
		
		public function getPort()
		{
			return $this->port;
		}
		
		public function isConnected()
		{
			return $this->connected;
		}
		
		/**
		 * @return SocketInputStream
		**/
		public function getInputStream()
		{
			$this->checkRead();
			
			return $this->inputStream;
		}
		
		/**
		 * @return SocketOutputStream
		**/
		public function getOutputStream()
		{
			$this->checkWrite();
			
			return $this->outputStream;
		}
		
		public function connect($connectTimeout = self::DEFAULT_TIMEOUT)
		{
			Assert::isTrue(
				isset($this->host) && isset($this->port),
				'set host and port first'
			);
			
			// TODO: assuming we are in blocking mode
			// for non-blocking mode this method must throw an exception,
			// use non-blocking socket channels instead
			
			socket_set_nonblock($this->socket);
			
			try {
				socket_connect($this->socket, $this->host, $this->port);
			} catch (BaseException $e) {
				/* yum-yum */
			}
			
			socket_set_block($this->socket);
			
			$r = array($this->socket);
			$w = array($this->socket);
			$e = array($this->socket);
			
			switch (
				socket_select(
					$r, $w, $e,
					(int)($connectTimeout / 1000),
					(int)($connectTimeout % 1000 * 1000)
				)
			) {
				case 0:
					throw new NetworkException(
						"unable to connect to '{$this->host}:{$this->port}': "
						."connection timed out"
					);
				
				case 1:
					$this->connected = true;
					break;
					
				case 2:
					// yanetut
					throw new NetworkException(
						"unable to connect to '{$this->host}:{$this->port}': "
						."connection refused"
					);
			}
			
			if (!$this->timeout)
				$this->setTimeout(self::DEFAULT_TIMEOUT);
			
			return $this;
		}
		
		public function setTimeout($timeout)
		{
			$timeVal = array(
				'sec' => (int)($timeout / 1000),
				'usec' => (int)($timeout % 1000 * 1000)
			);
			
			socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, $timeVal);
			
			$this->timeout = $timeout;
			
			return $this;
		}
		
		public function getTimeout()
		{
			// NOTE: return value may slightly differ from $this->timeout
			$timeVal = socket_get_option($this->socket, SOL_SOCKET, SO_SNDTIMEO);
			
			return $timeVal['sec'] * 1000 + (int)($timeVal['usec'] / 1000);
		}
		
		public function read($length)
		{
			$this->checkRead();
			
			socket_clear_error($this->socket);
			
			$result = socket_read($this->socket, $length);
			
			if ($result === false)
				throw new NetworkException(
					"socket reading failed: "
					.socket_strerror(socket_last_error())
				);
			
			return $result;
		}
		
		public function write($buffer, $length = null)
		{
			$this->checkWrite();
			
			socket_clear_error($this->socket);
			
			if ($length === null)
				$result = socket_write($this->socket, $buffer);
			else
				$result = socket_write($this->socket, $buffer, $length);
				
			if ($result === false)
				throw new NetworkException(
					"socket writing failed: "
					.socket_strerror(socket_last_error())
				);
			
			return $result;
		}
		
		public function isTimedOut()
		{
			if (socket_last_error($this->socket) === self::EAGAIN)
				return true;
			else
				return false;
		}
		
		public function shutdownInput()
		{
			socket_shutdown($this->socket, 0);
			
			$this->inputShutdown = true;
		}
		
		public function shutdownOutput()
		{
			socket_shutdown($this->socket, 1);
			
			$this->outputShutdown = true;
		}
		
		public function close()
		{
			if (!$this->inputShutdown)
				$this->shutdownInput();
				
			if (!$this->outputShutdown)
				$this->shutdownOutput();
				
			socket_close($this->socket);
			
			$this->closed = true;
		}
		
		private function checkRead()
		{
			if ($this->closed || !$this->connected || $this->inputShutdown)
				throw new NetworkException(
					'cannod read from socket: '
					.'it is closed, not connected, or has been shutdown'
				);
		}
		
		private function checkWrite()
		{
			if ($this->closed || !$this->connected || $this->inputShutdown)
				throw new NetworkException(
					'cannod write to socket: '
					.'it is closed, not connected, or has been shutdown'
				);
		}
	}
?>